<?php

declare (strict_types=1);
namespace Laminas\View\Helper\Service;

use Laminas\View\Config_Provider;
use Laminas\View\Factory\Configuration;
use Laminas\View\Helper\Doctype;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 * @psalm-import-type ViewConfigShape from ConfigProvider
 */
final readonly class Doctype_Factory
{
    public function __invoke(Container_Interface $container): Doctype
    {
        /** @var ViewConfigShape $config */
        $config = Configuration::get($container);
        $doctype = $config['view_manager']['doctype'] ?? Doctype::DEFAULT_DOCTYPE;
        $doctype = $config['view_helper_config']['doctype'] ?? $doctype;
        return new Doctype($doctype);
    }
}