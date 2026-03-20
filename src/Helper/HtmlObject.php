<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function implode;
use Laminas\Escaper\Escaper_Interface;
use Laminas\View\Html_Attributes_Set;
use const PHP_EOL;
use function sprintf;
final readonly class Html_Object
{
    public function __construct(private Escaper_Interface $escaper, private Doctype $doctype)
    {
    }
    /**
     * Output an object set
     *
     * @param string $data The data attribute - the URL of the resource
     * @param string $type The mime type of the target resource
     * @param array<string, scalar> $attributes HTML tag attributes
     * @param array<string, scalar> $params Parameters for the resource
     * @param string|null $content Fallback content. This content is not escaped and is assumed to be markup.
     */
    public function __invoke(string $data, string $type, array $attributes = [], array $params = [], string|null $content = null): string
    {
        $attributes = array_merge(['data' => $data, 'type' => $type], $attributes);
        $parameters = implode(PHP_EOL, array_map(fn(string $name, int|float|bool|string $value): string => sprintf('    <param name="%s" value="%s"%s>', $this->escaper->escape_html_attr($name), $this->escaper->escape_html_attr((string) $value), $this->doctype->is_xhtml() ? ' /' : ''), array_keys($params), array_values($params)));
        $attributes = (string) new Html_Attributes_Set($this->escaper, $attributes);
        return <<<HTML
        <object{$attributes}>
        {$parameters}
            {$content}
        </object>
        HTML;
    }
}