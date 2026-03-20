<?php

declare (strict_types=1);
namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\Partial;
use Laminas\View\Helper\Partial_Loop;
use Laminas\View\Helper_Plugin_Manager;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Partial_Loop_Factory
{
    public function __invoke(Container_Interface $container): Partial_Loop
    {
        $helpers = $container->get(Helper_Plugin_Manager::class);
        return new Partial_Loop($helpers->get(Partial::class));
    }
}