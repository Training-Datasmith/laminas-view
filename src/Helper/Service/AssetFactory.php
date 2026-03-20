<?php

declare (strict_types=1);
namespace Laminas\View\Helper\Service;

use function gettype;
use function is_array;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Factory\Configuration;
use Laminas\View\Helper\Asset;
use Psr\Container\Container_Interface;
use function sprintf;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Asset_Factory
{
    /**
     * @throws RuntimeException
     */
    public function __invoke(Container_Interface $container): Asset
    {
        $config = Configuration::get($container);
        $helper_config = $this->assert_array('view_helper_config', $config);
        $helper_config = $this->assert_array('asset', $helper_config);
        /** @psalm-var array<non-empty-string, non-empty-string> $resourceMap */
        $resource_map = $this->assert_array('resource_map', $helper_config);
        return new Asset($resource_map);
    }
    /**
     * @param array<array-key, mixed> $array
     * @return array<array-key, mixed>
     */
    private function assert_array(string $key, array $array): array
    {
        $value = $array[$key] ?? [];
        if (!is_array($value)) {
            throw new RuntimeException(sprintf('Invalid resource map configuration. ' . 'Expected the key "%s" to contain an array value but received "%s"', $key, gettype($value)));
        }
        return $value;
    }
}