<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use function array_filter;
use function array_map;
use function array_unshift;
use function array_values;
use function implode;
use function is_int;
use Laminas\Escaper\Escaper_Interface;
use Laminas\View\HTML\Tag;
use Laminas\View\Html_Attributes_Set;
use const PHP_EOL;
use function sprintf;
use function str_repeat;
use Stringable;
final class Head_Meta implements Stateful_Helper_Interface, Stringable
{
    /** @var list<Tag> */
    private array $items = [];
    private string $indent;
    private string $separator;
    public function __construct(private readonly Escaper_Interface $escaper, private readonly Doctype $doctype, private readonly string $default_separator = PHP_EOL, private readonly string $default_indent = '')
    {
        $this->indent = $this->default_indent;
        $this->separator = $this->default_separator;
    }
    public function reset_state(): void
    {
        $this->items = [];
        $this->indent = $this->default_indent;
        $this->separator = $this->default_separator;
    }
    /**
     * Retrieve object instance; optionally add meta tag
     *
     * @param array<string, scalar> $attributes
     */
    public function __invoke(string|null $name = null, string|null $content = null, array $attributes = []): self
    {
        if ($name !== null && $content !== null) {
            return $this->append_name($name, $content, $attributes);
        }
        if ($attributes === []) {
            return $this;
        }
        return $this->append($attributes);
    }
    /**
     * Render placeholder as string
     */
    public function to_string(string|int|null $indent = null): string
    {
        $indent = is_int($indent) ? str_repeat(' ', $indent) : $indent;
        $indent ??= $this->indent;
        $indent = $this->escaper->escape_html($indent);
        return implode($this->escaper->escape_html($this->separator), array_map(fn(Tag $tag): string => $indent . $this->item_to_string($tag), $this->items));
    }
    public function __toString(): string
    {
        return $this->to_string();
    }
    private function item_to_string(Tag $item): string
    {
        $attributes = new Html_Attributes_Set($this->escaper, $item->attributes);
        $closing = $this->doctype->is_xhtml() ? ' /' : '';
        return sprintf('<%s%s%s>', $item->tag, (string) $attributes, $closing);
    }
    /** @param array<string, scalar> $attributes */
    public function append(array $attributes): self
    {
        if ($attributes === []) {
            return $this;
        }
        $tag = new Tag('meta', $attributes);
        foreach ($this->items as $item) {
            if ($item->equals($tag)) {
                return $this;
            }
        }
        $this->items[] = $tag;
        return $this;
    }
    /** @param array<string, scalar> $attributes */
    public function prepend(array $attributes): self
    {
        if ($attributes === []) {
            return $this;
        }
        $tag = new Tag('meta', $attributes);
        foreach ($this->items as $item) {
            if ($item->equals($tag)) {
                return $this;
            }
        }
        array_unshift($this->items, $tag);
        return $this;
    }
    /**
     * Create an HTML5-style meta charset tag. Something like <meta charset="utf-8">
     */
    public function set_charset(string $charset): self
    {
        $this->clear_by_attribute('charset');
        $this->prepend(['charset' => $charset]);
        return $this;
    }
    /** @param array<string, scalar> $attributes */
    public function set_name(string $name, string $content, array $attributes = []): self
    {
        $this->clear_by_attribute_value('name', $name);
        $attributes['name'] = $name;
        $attributes['content'] = $content;
        return $this->append($attributes);
    }
    /** @param array<string, scalar> $attributes */
    public function append_name(string $name, string $content, array $attributes = []): self
    {
        $attributes['name'] = $name;
        $attributes['content'] = $content;
        return $this->append($attributes);
    }
    /** @param array<string, scalar> $attributes */
    public function prepend_name(string $name, string $content, array $attributes = []): self
    {
        $attributes['name'] = $name;
        $attributes['content'] = $content;
        return $this->prepend($attributes);
    }
    /** @param array<string, scalar> $attributes */
    public function set_itemprop(string $itemprop, string $content, array $attributes = []): self
    {
        $this->clear_by_attribute_value('itemprop', $itemprop);
        $attributes['itemprop'] = $itemprop;
        $attributes['content'] = $content;
        return $this->append($attributes);
    }
    /** @param array<string, scalar> $attributes */
    public function append_itemprop(string $itemprop, string $content, array $attributes = []): self
    {
        $attributes['itemprop'] = $itemprop;
        $attributes['content'] = $content;
        return $this->append($attributes);
    }
    /** @param array<string, scalar> $attributes */
    public function prepend_itemprop(string $itemprop, string $content, array $attributes = []): self
    {
        $attributes['itemprop'] = $itemprop;
        $attributes['content'] = $content;
        return $this->prepend($attributes);
    }
    /** @param array<string, scalar> $attributes */
    public function set_property(string $property, string $content, array $attributes = []): self
    {
        $this->clear_by_attribute_value('property', $property);
        $attributes['property'] = $property;
        $attributes['content'] = $content;
        return $this->append($attributes);
    }
    /** @param array<string, scalar> $attributes */
    public function append_property(string $property, string $content, array $attributes = []): self
    {
        $attributes['property'] = $property;
        $attributes['content'] = $content;
        return $this->append($attributes);
    }
    /** @param array<string, scalar> $attributes */
    public function prepend_property(string $property, string $content, array $attributes = []): self
    {
        $attributes['property'] = $property;
        $attributes['content'] = $content;
        return $this->prepend($attributes);
    }
    /** @param array<string, scalar> $attributes */
    public function set_http_equiv(string $http_equiv, string $content, array $attributes = []): self
    {
        $this->clear_by_attribute_value('http-equiv', $http_equiv);
        $attributes['http-equiv'] = $http_equiv;
        $attributes['content'] = $content;
        return $this->append($attributes);
    }
    /** @param array<string, scalar> $attributes */
    public function append_http_equiv(string $http_equiv, string $content, array $attributes = []): self
    {
        $attributes['http-equiv'] = $http_equiv;
        $attributes['content'] = $content;
        return $this->append($attributes);
    }
    /** @param array<string, scalar> $attributes */
    public function prepend_http_equiv(string $http_equiv, string $content, array $attributes = []): self
    {
        $attributes['http-equiv'] = $http_equiv;
        $attributes['content'] = $content;
        return $this->prepend($attributes);
    }
    private function clear_by_attribute(string $attribute): void
    {
        $this->items = array_values(array_filter($this->items, static fn(Tag $item): bool => !$item->has_attribute($attribute)));
    }
    private function clear_by_attribute_value(string $name, int|string|float|bool $value): void
    {
        $this->items = array_values(array_filter($this->items, static fn(Tag $item): bool => !$item->has_attribute($name) || $item->get_attribute($name) !== $value));
    }
    public function set_indent(string|int $indent): self
    {
        $this->indent = is_int($indent) ? str_repeat(' ', $indent) : $indent;
        return $this;
    }
    public function set_separator(string $separator): self
    {
        $this->separator = $separator;
        return $this;
    }
}