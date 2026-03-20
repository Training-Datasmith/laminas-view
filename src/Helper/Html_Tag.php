<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use Laminas\Escaper\Escaper_Interface;
use Laminas\View\Html_Attributes_Set;
use function sprintf;
/**
 * Renders <html> tag (both opening and closing) of a web page, to which some custom
 * attributes can be added dynamically.
 */
final class Html_Tag implements Stateful_Helper_Interface
{
    /**
     * Attributes for the <html> tag.
     *
     * @var array<string, scalar>
     */
    private array $attributes = [];
    /**
     * Whether to add the relevant namespaces depending on the doctype
     */
    private bool $add_namespace = false;
    public function __construct(private readonly Escaper_Interface $escaper, private readonly Doctype $doctype)
    {
    }
    public function reset_state(): void
    {
        $this->attributes = [];
        $this->add_namespace = false;
    }
    /**
     * Retrieve object instance; optionally add attributes.
     *
     * @param array<string, scalar> $attributes
     */
    public function __invoke(array $attributes = []): self
    {
        if ($attributes !== []) {
            $this->attributes = $attributes;
        }
        return $this;
    }
    /**
     * Add an attribute to the <html> tag
     */
    public function set_attribute(string $name, string $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }
    /**
     * Add new or overwrite the existing attributes.
     *
     * @param array<string, scalar> $attributes
     */
    public function set_attributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }
    public function add_xhtml_namespace(bool $flag): self
    {
        $this->add_namespace = $flag;
        return $this;
    }
    /**
     * Render opening tag.
     */
    public function open_tag(): string
    {
        $attributes = $this->attributes;
        if ($this->doctype->is_xhtml() && $this->add_namespace) {
            $attributes['xmlns'] = 'https://www.w3.org/1999/xhtml';
        }
        return sprintf('<html%s>', new Html_Attributes_Set($this->escaper, $attributes));
    }
    /**
     * Render closing tag.
     */
    public function close_tag(): string
    {
        return '</html>';
    }
}