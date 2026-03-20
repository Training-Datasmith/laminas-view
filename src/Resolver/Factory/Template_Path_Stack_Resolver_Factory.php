<?php

declare (strict_types=1);
namespace Laminas\View\Resolver\Factory;

use Laminas\View\Config_Provider;
use Laminas\View\Factory\Configuration;
use Laminas\View\Resolver\Template_Path_Stack;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 * @psalm-import-type ViewConfigShape from ConfigProvider
 */
final readonly class Template_Path_Stack_Resolver_Factory
{
    public function __invoke(Container_Interface $container): Template_Path_Stack
    {
        /** @var ViewConfigShape $config */
        $config = Configuration::get($container);
        $paths = $config['view_manager']['template_path_stack'] ?? [];
        return new Template_Path_Stack(['default_suffix' => Configuration::default_template_suffix($container), 'script_paths' => $paths]);
    }
}