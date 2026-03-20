<?php

declare (strict_types=1);
namespace Laminas\View\Factory;

use Laminas\Escaper\Escaper;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Escaper_Factory
{
    public function __invoke(Container_Interface $container): Escaper
    {
        return new Escaper(Configuration::view_encoding($container));
    }
}