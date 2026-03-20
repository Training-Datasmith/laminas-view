<?php

declare (strict_types=1);
namespace Laminas\View\Factory;

use Laminas\View\Helper_Plugin_Manager_Interface;
use Laminas\View\Renderer\Renderer_Interface;
use Laminas\View\View;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class View_Factory
{
    public function __invoke(Container_Interface $container): View
    {
        return new View($container->get(Renderer_Interface::class), $container->get(Helper_Plugin_Manager_Interface::class), Configuration::default_layout($container), Configuration::default_capture_to($container));
    }
}