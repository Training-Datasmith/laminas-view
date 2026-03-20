<?php

declare (strict_types=1);
namespace Laminas\View\Resolver\Factory;

use Laminas\View\Config_Provider;
use Laminas\View\Factory\Configuration;
use Laminas\View\Resolver\Prefix_Path_Stack_Resolver;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 * @psalm-import-type ViewConfigShape from ConfigProvider
 */
final readonly class Prefix_Path_Stack_Resolver_Factory
{
    public function __invoke(Container_Interface $container): Prefix_Path_Stack_Resolver
    {
        /** @var ViewConfigShape $config */
        $config = Configuration::get($container);
        $paths = $config['view_manager']['prefix_template_path_stack'] ?? [];
        return new Prefix_Path_Stack_Resolver($paths, Configuration::default_template_suffix($container));
    }
}