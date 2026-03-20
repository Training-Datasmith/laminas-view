<?php

declare (strict_types=1);
namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\Render_To_Placeholder;
use Laminas\View\Helper_Plugin_Manager;
use Laminas\View\Renderer\Php_Renderer;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Render_To_Placeholder_Factory
{
    public function __invoke(Container_Interface $container): Render_To_Placeholder
    {
        $helpers = $container->get(Helper_Plugin_Manager::class);
        return new Render_To_Placeholder($container->get(Php_Renderer::class), $helpers->get(Placeholder::class));
    }
}