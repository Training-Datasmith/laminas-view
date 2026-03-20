<?php

declare (strict_types=1);
namespace Laminas\View\Factory;

use Laminas\View\Config_Provider;
use Laminas\View\Helper_Plugin_Manager;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-import-type ViewConfigShape from ConfigProvider
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Helper_Plugin_Manager_Factory
{
    public function __invoke(Container_Interface $container): Helper_Plugin_Manager
    {
        /** @var ViewConfigShape $applicationConfig */
        $application_config = Configuration::get($container);
        $helper_config = $application_config['view_helpers'] ?? [];
        return new Helper_Plugin_Manager($container, $helper_config);
    }
}