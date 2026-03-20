<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use function assert;
use function implode;
use function is_int;
use function is_string;
use Laminas\Escaper\Escaper_Interface;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Placeholder\Container;
use Laminas\View\Helper\Placeholder\Position;
use Laminas\View\HTML\Tag;
use Laminas\View\Html_Attributes_Set;
use const PHP_EOL;
use function preg_replace;
use function sprintf;
use function str_repeat;
use Stringable;
use function trim;
/**
 * This class is not designed for user inheritance and should be considered internal.
 *
 * @psalm-inheritors HeadScript|InlineScript
 */
abstract class Abstract_Javascript_Helper implements Stateful_Helper_Interface, Stringable
{
    /** @var Container<Tag> */
    private Container $container;
    private string $separator;
    private string $indent;
    private Position|null $capture_position = null;
    /** @var array<string, scalar> */
    private array $capture_attributes = [];
    final public function __construct(private readonly Escaper_Interface $escaper, private readonly Doctype $doctype, private readonly string $default_separator = PHP_EOL, private readonly string $default_indent = '')
    {
        /** @psalm-var Container<Tag> */
        $this->container = new Container();
        $this->separator = $this->default_separator;
        $this->indent = $this->default_indent;
    }
    final public function reset_state(): void
    {
        if ($this->container->is_capturing()) {
            $this->container->capture_end();
        }
        /** @psalm-var Container<Tag> */
        $this->container = new Container();
        $this->separator = $this->default_separator;
        $this->indent = $this->default_indent;
        $this->capture_position = null;
        $this->capture_attributes = [];
    }
    /** Instance Accessor */
    final public function __invoke(): self
    {
        return $this;
    }
    /** @param array<string, scalar> $attributes */
    private function is_empty_script(string|null $content, array $attributes): bool
    {
        return ($content === null || trim($content) === '') && (!isset($attributes['src']) || !is_string($attributes['src']) || $attributes['src'] === '');
    }
    /** @param array<string, scalar> $attributes */
    final public function append_script(string $content, array $attributes = []): self
    {
        if ($this->is_empty_script($content, $attributes)) {
            return $this;
        }
        $this->container->append(new Tag('script', $attributes, $content));
        return $this;
    }
    /** @param array<string, scalar> $attributes */
    final public function prepend_script(string $content, array $attributes = []): self
    {
        if ($this->is_empty_script($content, $attributes)) {
            return $this;
        }
        $this->container->prepend(new Tag('script', $attributes, $content));
        return $this;
    }
    /** @param array<string, scalar> $attributes */
    final public function set_script(string $content, array $attributes = []): self
    {
        if ($this->is_empty_script($content, $attributes)) {
            return $this;
        }
        $this->container->set(new Tag('script', $attributes, $content));
        return $this;
    }
    /**
     * When a file src is added, make sure it is not duplicated
     *
     * @param non-empty-string $src
     */
    private function remove_matching_src(string $src): void
    {
        $match = $this->container->first_match(static fn(Tag $tag): bool => ($tag->attributes['src'] ?? null) === $src);
        if ($match === null) {
            return;
        }
        $this->container->remove($match);
    }
    /**
     * @param non-empty-string $src
     * @param array<string, scalar> $attributes
     */
    final public function append_file(string $src, array $attributes = []): self
    {
        $this->remove_matching_src($src);
        $attributes['src'] = $src;
        $this->container->append(new Tag('script', $attributes));
        return $this;
    }
    /**
     * @param non-empty-string $src
     * @param array<string, scalar> $attributes
     */
    final public function prepend_file(string $src, array $attributes = []): self
    {
        $this->remove_matching_src($src);
        $attributes['src'] = $src;
        $this->container->prepend(new Tag('script', $attributes));
        return $this;
    }
    /**
     * @param non-empty-string $src
     * @param array<string, scalar> $attributes
     */
    final public function set_file(string $src, array $attributes = []): self
    {
        $attributes['src'] = $src;
        $this->container->set(new Tag('script', $attributes));
        return $this;
    }
    /** @param int<1, max>|string $indent */
    final public function set_indent(int|string $indent): self
    {
        $this->indent = is_int($indent) ? str_repeat(' ', $indent) : $indent;
        return $this;
    }
    final public function set_separator(string $separator): self
    {
        $this->separator = $separator;
        return $this;
    }
    /**
     * Render aggregated style tags to a string
     *
     * @param int<1, max>|string|null $indent
     */
    final public function to_string(int|string|null $indent = null): string
    {
        if ($indent !== null) {
            $this->set_indent($indent);
        }
        $items = [];
        foreach ($this->container as $item) {
            $items[] = $this->item_to_string($item);
        }
        $content = implode($this->separator, $items);
        $content = preg_replace("/(\r\n?|\n)/", '$1' . $this->indent, $content);
        assert(is_string($content));
        return $this->indent . $content;
    }
    final public function __toString(): string
    {
        return $this->to_string();
    }
    /**
     * Start capturing JS to the output buffer
     *
     * @param array<string, scalar> $attributes Optional script tag attributes
     * @throws RuntimeException
     */
    final public function capture_start(Position $position = Position::Append, array $attributes = []): void
    {
        $this->container->capture_start();
        $this->capture_position = $position;
        $this->capture_attributes = $attributes;
    }
    /**
     * Finish capturing the output buffer and store the content as a script tag
     */
    final public function capture_end(): void
    {
        $content = $this->container->capture_end();
        assert($this->capture_position !== null);
        match ($this->capture_position) {
            Position::Append => $this->append_script($content, $this->capture_attributes),
            Position::Prepend => $this->prepend_script($content, $this->capture_attributes),
            Position::Set => $this->set_script($content, $this->capture_attributes),
        };
        $this->capture_position = null;
        $this->capture_attributes = [];
    }
    private function item_to_string(Tag $item): string
    {
        $attributes = $item->attributes;
        // Add the `type` attribute if otherwise unset and the doctype is not HTML5
        if (!$this->doctype->is_html5() && !isset($attributes['type'])) {
            $attributes['type'] = 'text/javascript';
        }
        return sprintf('<script%s>%s</script>', (string) new Html_Attributes_Set($this->escaper, $attributes), $item->content === null ? '' : PHP_EOL . $item->content . PHP_EOL);
    }
}