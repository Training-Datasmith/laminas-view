<?php

declare (strict_types=1);
namespace Laminas\View\Resolver\Factory;

use Laminas\View\Config_Provider;
use Laminas\View\Factory\Configuration;
use Laminas\View\Resolver\Template_Map_Resolver;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 * @psalm-import-type ViewConfigShape from ConfigProvider
 */
final readonly class Template_Map_Resolver_Factory
{
    public function __invoke(Container_Interface $container): Template_Map_Resolver
    {
        /** @var ViewConfigShape $config */
        $config = Configuration::get($container);
        /**
         * In laminas MVC applications, we find the template map under `view_manager.template_map`
         */
        $mvc_map = $config['view_manager']['template_map'] ?? [];
        /**
         * In Mezzio applications, the template map can be found under `templates.map`
         */
        $mezzio_map = $config['templates']['map'] ?? [];
        $map_resolver = new Template_Map_Resolver();
        $map_resolver->add($mvc_map);
        $map_resolver->add($mezzio_map);
        return $map_resolver;
    }
}