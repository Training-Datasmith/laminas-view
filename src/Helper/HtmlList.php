<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use function is_array;
use Laminas\Escaper\Escaper_Interface;
use Laminas\View\Exception;
use Laminas\View\Html_Attributes_Set;
use const PHP_EOL;
use function sprintf;
use function strlen;
use function substr;
/**
 * Helper for ordered and unordered lists
 *
 * @psalm-import-type AttributeSet from HtmlAttributesSet
 */
final readonly class Html_List
{
    public function __construct(private Escaper_Interface $escaper)
    {
    }
    /**
     * Generates a 'List' element.
     *
     * @param  array<array-key, scalar|array> $items Array with the elements of the list
     * @param  bool                           $ordered Specifies ordered/unordered list; default unordered
     * @param  AttributeSet|null              $attribs Attributes for the ol/ul tag.
     * @param  bool                           $escape Whether to Escape the items.
     * @throws Exception\InvalidArgumentException If $items is empty.
     * @return string The list XHTML.
     */
    public function __invoke(array $items, bool $ordered = false, array|null $attribs = null, bool $escape = true): string
    {
        if ($items === []) {
            throw new Exception\InvalidArgumentException(sprintf('$items array can not be empty in %s', __METHOD__));
        }
        $list = '';
        foreach ($items as $item) {
            if (!is_array($item)) {
                $markup = $escape ? $this->escaper->escape_html((string) $item) : (string) $item;
                $list .= '<li>' . $markup . '</li>' . PHP_EOL;
            } else {
                /** @psalm-var list<scalar|list<scalar>> $item */
                $item_length = strlen('</li>' . PHP_EOL);
                if ($item_length < strlen($list)) {
                    $list = substr($list, 0, strlen($list) - $item_length) . $this->__invoke($item, $ordered, $attribs, $escape) . '</li>' . PHP_EOL;
                } else {
                    $list .= '<li>' . $this->__invoke($item, $ordered, $attribs, $escape) . '</li>' . PHP_EOL;
                }
            }
        }
        $attributes = is_array($attribs) ? (string) new Html_Attributes_Set($this->escaper, $attribs) : '';
        $tag = $ordered ? 'ol' : 'ul';
        return '<' . $tag . $attributes . '>' . PHP_EOL . $list . '</' . $tag . '>' . PHP_EOL;
    }
}