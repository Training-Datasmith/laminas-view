<?php

declare (strict_types=1);
namespace Laminas\View\Helper\Service;

use function is_array;
use function is_bool;
use function is_string;
use Laminas\Escaper\Escaper;
use Laminas\Escaper\Escaper_Interface;
use Laminas\View\Factory\Configuration;
use Laminas\View\Helper\Head_Title;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Head_Title_Factory
{
    public function __invoke(Container_Interface $container): Head_Title
    {
        $config = Configuration::get($container);
        $options = $this->resolve_options($config);
        $escaper = $container->has(Escaper_Interface::class) ? $container->get(Escaper_Interface::class) : new Escaper();
        return new Head_Title($escaper, $options['auto_escape'], $options['separator'], $options['indent'], $options['prefix'], $options['postfix'], Fetch_Translator_From_Container::with_historic_aliases($container), $options['text_domain']);
    }
    /**
     * @param array<array-key, mixed> $config
     * @return array{
     *     indent: string,
     *     separator: string,
     *     prefix: string,
     *     postfix: string,
     *     auto_escape: bool,
     *     text_domain: non-empty-string,
     * }
     */
    private function resolve_options(array $config): array
    {
        $options = ['indent' => '', 'separator' => '', 'prefix' => '', 'postfix' => '', 'auto_escape' => true, 'text_domain' => 'default'];
        if (!isset($config['view_helper_config']) || !is_array($config['view_helper_config'])) {
            return $options;
        }
        $config = $config['view_helper_config'];
        if (!isset($config['head_title']) || !is_array($config['head_title'])) {
            return $options;
        }
        $config = $config['head_title'];
        $options['indent'] = isset($config['indent']) && is_string($config['indent']) ? $config['indent'] : $options['indent'];
        $options['separator'] = isset($config['separator']) && is_string($config['separator']) ? $config['separator'] : $options['separator'];
        $options['prefix'] = isset($config['prefix']) && is_string($config['prefix']) ? $config['prefix'] : $options['prefix'];
        $options['postfix'] = isset($config['postfix']) && is_string($config['postfix']) ? $config['postfix'] : $options['postfix'];
        $options['auto_escape'] = isset($config['auto_escape']) && is_bool($config['auto_escape']) ? $config['auto_escape'] : $options['auto_escape'];
        $options['text_domain'] = isset($config['text_domain']) && is_string($config['text_domain']) && $config['text_domain'] !== '' ? $config['text_domain'] : $options['text_domain'];
        return $options;
    }
}