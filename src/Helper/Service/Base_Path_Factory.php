<?php

declare (strict_types=1);
namespace Laminas\View\Helper\Service;

use function assert;
use function is_string;
use Laminas\View\Config_Provider;
use Laminas\View\Factory\Configuration;
use Laminas\View\Helper\Base_Path;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 * @psalm-import-type ViewConfigShape from ConfigProvider
 */
final readonly class Base_Path_Factory
{
    public function __invoke(Container_Interface $container): Base_Path
    {
        /** @var ViewConfigShape $config */
        $config = Configuration::get($container);
        // The expected location in config for the base path in an MVC application
        // is config.view_manager.base_path
        // @link https://docs.laminas.dev/laminas-mvc/services/#viewmanager
        // More recently, we look in `view_helper_config.base_path`
        $base_path = $config['view_manager']['base_path'] ?? null;
        $base_path = $config['view_helper_config']['base_path'] ?? $base_path;
        assert(is_string($base_path) || $base_path === null);
        return new Base_Path($base_path);
    }
}