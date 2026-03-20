<?php

declare (strict_types=1);
namespace Laminas\View\Helper\Service;

use Laminas\View\Helper\Layout;
use Laminas\View\Helper\View_Model;
use Laminas\View\Helper_Plugin_Manager;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Layout_Factory
{
    public function __invoke(Container_Interface $container): Layout
    {
        $helpers = $container->get(Helper_Plugin_Manager::class);
        return new Layout($helpers->get(View_Model::class));
    }
}