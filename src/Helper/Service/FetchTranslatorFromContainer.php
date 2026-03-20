<?php

declare (strict_types=1);
namespace Laminas\View\Helper\Service;

use Laminas\Translator\Translator_Interface;
use Psr\Container\Container_Interface;
/**
 * @internal
 *
 * @psalm-internal Laminas
 * @psalm-internal LaminasTest
 */
final readonly class Fetch_Translator_From_Container
{
    public const SEARCH_ALIASES = [
        Translator_Interface::class,
        'MvcTranslator',
        'Laminas\I18n\Translator\TranslatorInterface',
        // phpcs:ignore
        'Translator',
    ];
    public static function with_historic_aliases(Container_Interface $container): Translator_Interface|null
    {
        foreach (self::SEARCH_ALIASES as $alias) {
            if (!$container->has($alias)) {
                continue;
            }
            $service = $container->get($alias);
            if (!$service instanceof Translator_Interface) {
                continue;
            }
            return $service;
        }
        return null;
    }
}