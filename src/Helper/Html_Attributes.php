<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use Laminas\Escaper\Escaper_Interface;
use Laminas\View\Html_Attributes_Set;
/**
 * Helper for creating HtmlAttributesSet objects
 */
final readonly class Html_Attributes
{
    public function __construct(private Escaper_Interface $escaper)
    {
    }
    /**
     * Returns a new HtmlAttributesSet object, optionally initializing it with
     * the provided value.
     *
     * @param iterable<string, scalar|array|null> $attributes
     */
    public function __invoke(iterable $attributes = []): Html_Attributes_Set
    {
        return new Html_Attributes_Set($this->escaper, $attributes);
    }
}