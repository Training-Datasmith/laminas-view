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
use function str_repeat;
use Stringable;
/**
 * Helper for adding inline CSS to the head in style tags
 */
final class Head_Style implements Stateful_Helper_Interface, Stringable
{
    /** @var Container<Tag> */
    private Container $container;
    private string $separator;
    private string $indent;
    private Position|null $capture_position = null;
    /** @var array<string, scalar> */
    private array $capture_attributes = [];
    public function __construct(private readonly Escaper_Interface $escaper, private readonly Doctype $doctype, private readonly string $default_separator = PHP_EOL, private readonly string $default_indent = '')
    {
        /** @psalm-var Container<Tag> */
        $this->container = new Container();
        $this->separator = $this->default_separator;
        $this->indent = $this->default_indent;
    }
    public function reset_state(): void
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
    /**
     * Return headStyle object
     *
     * Returns headStyle helper object; optionally, appends a new style element to the list
     *
     * @param string|null $content CSS to add to a style element
     * @param array<string, scalar> $attributes to apply to the style element
     */
    public function __invoke(string|null $content = null, array $attributes = [], Position $position = Position::Append): self
    {
        if ($content !== null) {
            match ($position) {
                Position::Append => $this->append_style($content, $attributes),
                Position::Prepend => $this->prepend_style($content, $attributes),
                Position::Set => $this->set_style($content, $attributes),
            };
        }
        return $this;
    }
    /** @param array<string, scalar> $attributes */
    public function append_style(string $content, array $attributes = []): self
    {
        if ($content === '') {
            return $this;
        }
        $this->container->append(new Tag('style', $attributes, $content));
        return $this;
    }
    /** @param array<string, scalar> $attributes */
    public function prepend_style(string $content, array $attributes = []): self
    {
        if ($content === '') {
            return $this;
        }
        $this->container->prepend(new Tag('style', $attributes, $content));
        return $this;
    }
    /** @param array<string, scalar> $attributes */
    public function set_style(string $content, array $attributes = []): self
    {
        if ($content === '') {
            return $this;
        }
        $this->container->set(new Tag('style', $attributes, $content));
        return $this;
    }
    /** @param int<1, max>|string $indent */
    public function set_indent(int|string $indent): self
    {
        $this->indent = is_int($indent) ? str_repeat(' ', $indent) : $indent;
        return $this;
    }
    public function set_separator(string $separator): self
    {
        $this->separator = $separator;
        return $this;
    }
    /**
     * Render aggregated style tags to a string
     *
     * @param int<1, max>|string|null $indent
     */
    public function to_string(int|string|null $indent = null): string
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
    public function __toString(): string
    {
        return $this->to_string();
    }
    /**
     * Start capturing CSS to the output buffer
     *
     * @param array<string, scalar> $attributes Optional style tag attributes
     * @throws RuntimeException
     */
    public function capture_start(Position $position = Position::Append, array $attributes = []): void
    {
        $this->container->capture_start();
        $this->capture_position = $position;
        $this->capture_attributes = $attributes;
    }
    /**
     * Finish capturing the output buffer and store the content as a style tag
     */
    public function capture_end(): void
    {
        $content = $this->container->capture_end();
        assert($this->capture_position !== null);
        match ($this->capture_position) {
            Position::Append => $this->append_style($content, $this->capture_attributes),
            Position::Prepend => $this->prepend_style($content, $this->capture_attributes),
            Position::Set => $this->set_style($content, $this->capture_attributes),
        };
        $this->capture_position = null;
        $this->capture_attributes = [];
    }
    private function item_to_string(Tag $item): string
    {
        assert($item->content !== null);
        $attributes = $item->attributes;
        if (!$this->doctype->is_html5()) {
            $attributes['type'] = 'text/css';
        }
        $attributes = (string) new Html_Attributes_Set($this->escaper, $attributes);
        return <<<HTML
        <style{$attributes}>
        {$item->content}
        </style>
        HTML;
    }
}