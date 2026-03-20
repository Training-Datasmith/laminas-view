<?php

declare (strict_types=1);
namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\Partial;
use Laminas\View\Renderer\Php_Renderer;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Partial_Factory
{
    public function __invoke(Container_Interface $container): Partial
    {
        return new Partial($container->get(Php_Renderer::class));
    }
}