<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use function array_map;
use function array_unshift;
use Closure;
use function implode;
use function is_int;
use Laminas\Escaper\Escaper;
use Laminas\Escaper\Escaper_Interface;
use Laminas\Translator\Translator_Interface;
use function sprintf;
use function str_repeat;
use Stringable;
/**
 * Helper for setting and retrieving title element for HTML head.
 */
final class Head_Title implements Stringable, Stateful_Helper_Interface
{
    /** @var list<string> $items */
    private array $items = [];
    private readonly Escaper_Interface $escaper;
    private string|null $separator;
    private string|null $indent;
    private string|null $prefix;
    private string|null $postfix;
    /**
     * @param non-empty-string $translatorTextDomain
     */
    public function __construct(Escaper_Interface|null $escaper = null, private readonly bool $auto_escape = true, private readonly string $default_separator = '', private readonly string $default_indent = '', private readonly string $default_prefix = '', private readonly string $default_postfix = '', private readonly Translator_Interface|null $translator = null, private readonly string $translator_text_domain = 'default')
    {
        $this->escaper = $escaper ?? new Escaper();
        $this->separator = null;
        $this->indent = null;
        $this->prefix = null;
        $this->postfix = null;
    }
    public function reset_state(): void
    {
        $this->items = [];
        $this->separator = null;
        $this->indent = null;
        $this->prefix = null;
        $this->postfix = null;
    }
    public function __invoke(string|null $title = null): self
    {
        if ($title !== null && $title !== '') {
            $this->append($title);
        }
        return $this;
    }
    public function append(string $value): self
    {
        $this->items[] = $value;
        return $this;
    }
    public function prepend(string $value): self
    {
        array_unshift($this->items, $value);
        return $this;
    }
    public function set(string $value): self
    {
        $this->items = [$value];
        return $this;
    }
    public function set_indent(string|int $indent): self
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }
        $this->indent = $indent;
        return $this;
    }
    public function set_separator(string $separator): self
    {
        $this->separator = $separator;
        return $this;
    }
    public function set_prefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }
    public function set_postfix(string $postfix): self
    {
        $this->postfix = $postfix;
        return $this;
    }
    public function to_string(): string
    {
        return sprintf('%s<title>%s</title>', $this->indent ?? $this->default_indent, $this->render_title());
    }
    public function __toString(): string
    {
        return $this->to_string();
    }
    public function render_title(): string
    {
        $items = array_map($this->translator_callback()(...), $this->items);
        $content = sprintf('%s%s%s', $this->prefix ?? $this->default_prefix, implode($this->separator ?? $this->default_separator, $items), $this->postfix ?? $this->default_postfix);
        return $this->auto_escape ? $this->escaper->escape_html($content) : $content;
    }
    /**
     * Create and return a callback for translation of the title items
     *
     * @return Closure(string): string
     */
    private function translator_callback(): Closure
    {
        $translator = $this->translator;
        if ($translator === null) {
            return static fn(string $value): string => $value;
        }
        return fn(string $value): string => $translator->translate($value, $this->translator_text_domain);
    }
}