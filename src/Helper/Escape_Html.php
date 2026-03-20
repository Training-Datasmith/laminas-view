<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

final readonly class Escape_Html extends Escaper\Abstract_Helper
{
    protected function escape(string $value): string
    {
        return $this->escaper->escape_html($value);
    }
}