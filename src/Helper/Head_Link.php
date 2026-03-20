<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use function array_map;
use function array_values;
use function implode;
use Laminas\Escaper\Escaper_Interface;
use Laminas\View\HTML\Tag;
use Laminas\View\Html_Attributes_Set;
use const PHP_EOL;
use function sprintf;
use Stringable;
final class Head_Link implements Stateful_Helper_Interface, Stringable
{
    /** @var list<Tag> */
    private array $items;
    private string $indent;
    private string $separator;
    public function __construct(private readonly Escaper_Interface $escaper, private readonly Doctype $doctype, private readonly string $default_separator = PHP_EOL, private readonly string $default_indent = '')
    {
        $this->items = [];
        $this->indent = $this->default_indent;
        $this->separator = $this->default_separator;
    }
    public function reset_state(): void
    {
        $this->items = [];
        $this->separator = $this->default_separator;
        $this->indent = $this->default_indent;
    }
    /**
     * Allows helper retrieval from a template, optionally appending a new <link> element to the list
     *
     * @param array<string, scalar>|null $attributes
     */
    public function __invoke(array|null $attributes = null): self
    {
        if ($attributes !== null) {
            $this->append_item($this->create_item($attributes));
        }
        return $this;
    }
    public function set_indent(string $indent): self
    {
        $this->indent = $indent;
        return $this;
    }
    public function set_separator(string $separator): self
    {
        $this->separator = $separator;
        return $this;
    }
    /**
     * Append a link with any specification
     *
     * @param array<string, scalar> $attributes
     */
    public function append(array $attributes): self
    {
        $this->append_item($this->create_item($attributes));
        return $this;
    }
    /**
     * Prepend a link with any specification
     *
     * @param array<string, scalar> $attributes
     */
    public function prepend(array $attributes): self
    {
        $this->prepend_item($this->create_item($attributes));
        return $this;
    }
    /**
     * Reset the list with the provided link specification
     *
     * @param array<string, scalar> $attributes
     */
    public function set(array $attributes): self
    {
        $this->set_item($this->create_item($attributes));
        return $this;
    }
    /** @param array<string, scalar> $attributes */
    private function create_item(array $attributes): Tag
    {
        return new Tag('link', $attributes);
    }
    /**
     * @param non-empty-string $href
     * @param array<string, scalar> $attributes
     */
    private function stylesheet(string $href, array $attributes): Tag
    {
        $attributes['rel'] = 'stylesheet';
        $attributes['href'] = $href;
        $attributes['type'] = 'text/css';
        return $this->create_item($attributes);
    }
    /**
     * @param non-empty-string $href
     * @param array<string, scalar> $attributes
     */
    public function append_stylesheet(string $href, array $attributes = []): self
    {
        $this->append_item($this->stylesheet($href, $attributes));
        return $this;
    }
    /**
     * @param non-empty-string $href
     * @param array<string, scalar> $attributes
     */
    public function prepend_stylesheet(string $href, array $attributes = []): self
    {
        $this->prepend_item($this->stylesheet($href, $attributes));
        return $this;
    }
    /**
     * @param non-empty-string $href
     * @param array<string, scalar> $attributes
     */
    public function set_stylesheet(string $href, array $attributes = []): self
    {
        $this->set_item($this->stylesheet($href, $attributes));
        return $this;
    }
    private function append_item(Tag $item): void
    {
        $this->unset_matching_item($item);
        $this->items[] = $item;
    }
    private function prepend_item(Tag $item): void
    {
        $this->unset_matching_item($item);
        $this->items = [$item, ...$this->items];
    }
    private function set_item(Tag $item): void
    {
        $this->items = [$item];
    }
    private function unset_matching_item(Tag $item): void
    {
        $list = $this->items;
        foreach ($list as $index => $tag) {
            if (isset($tag->attributes['rel']) && isset($item->attributes['rel']) && $tag->attributes['rel'] !== $item->attributes['rel']) {
                continue;
            }
            if (isset($tag->attributes['href']) && isset($item->attributes['href']) && $tag->attributes['href'] !== $item->attributes['href']) {
                continue;
            }
            unset($list[$index]);
            break;
        }
        $this->items = array_values($list);
    }
    /**
     * Create HTML link element from data item
     */
    private function item_to_string(Tag $item): string
    {
        if ($item->attributes === []) {
            return '';
        }
        $attributes = new Html_Attributes_Set($this->escaper, $item->attributes);
        return sprintf('<%s%s%s>', $item->tag, (string) $attributes, $this->doctype->is_xhtml() ? ' /' : '');
    }
    public function to_string(string|null $indent = null): string
    {
        $indent = $this->escaper->escape_html($indent ?? $this->indent);
        return implode($this->separator, array_map(fn(Tag $tag): string => $indent . $this->item_to_string($tag), $this->items));
    }
    public function __toString(): string
    {
        return $this->to_string();
    }
}