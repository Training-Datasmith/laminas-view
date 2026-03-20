<?php

declare (strict_types=1);
namespace Laminas\View\Resolver\Factory;

use Laminas\View\Helper\View_Model;
use Laminas\View\Helper_Plugin_Manager;
use Laminas\View\Resolver\Aggregate_Resolver;
use Laminas\View\Resolver\Prefix_Path_Stack_Resolver;
use Laminas\View\Resolver\Relative_Fallback_Resolver;
use Laminas\View\Resolver\Template_Map_Resolver;
use Laminas\View\Resolver\Template_Path_Stack;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Aggregate_Resolver_Factory
{
    public function __invoke(Container_Interface $container): Aggregate_Resolver
    {
        $map_resolver = $container->get(Template_Map_Resolver::class);
        $stack_resolver = $container->get(Template_Path_Stack::class);
        $prefix_resolver = $container->get(Prefix_Path_Stack_Resolver::class);
        $plugin_manager = $container->get(Helper_Plugin_Manager::class);
        $view_model_helper = $plugin_manager->get(View_Model::class);
        return new Aggregate_Resolver([$map_resolver, $stack_resolver, $prefix_resolver, new Relative_Fallback_Resolver($map_resolver, $view_model_helper), new Relative_Fallback_Resolver($stack_resolver, $view_model_helper), new Relative_Fallback_Resolver($prefix_resolver, $view_model_helper)]);
    }
}