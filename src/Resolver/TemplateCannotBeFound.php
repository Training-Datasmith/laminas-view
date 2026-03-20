<?php

declare (strict_types=1);
namespace Laminas\View\Resolver;

use Laminas\View\Exception\RuntimeException;
use function sprintf;
/**
 * phpcs:disable WebimpressCodingStandard.NamingConventions.Exception
 */
final class Template_Cannot_Be_Found extends RuntimeException
{
    public static function by_name(string $name): self
    {
        return new self(sprintf('The template "%s" cannot be resolved', $name));
    }
}