<?php

declare (strict_types=1);
namespace Laminas\View\Renderer;

use Laminas\View\Factory\Configuration;
use Laminas\View\Helper_Plugin_Manager_Interface;
use Laminas\View\Resolver\Resolver_Interface;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas
 * @psalm-internal LaminasTest
 */
final readonly class Php_Renderer_Factory
{
    public function __invoke(Container_Interface $container): Php_Renderer
    {
        return new Php_Renderer($container->get(Helper_Plugin_Manager_Interface::class), $container->get(Resolver_Interface::class), Configuration::strict_variables($container));
    }
}