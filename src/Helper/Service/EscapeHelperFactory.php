<?php

declare (strict_types=1);
namespace Laminas\View\Helper\Service;

use function implode;
use Laminas\Escaper\Escaper;
use Laminas\Escaper\Escaper_Interface;
use Laminas\Service_Manager\Factory\Factory_Interface;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\Escape_Css;
use Laminas\View\Helper\Escape_Html;
use Laminas\View\Helper\Escape_Html_Attr;
use Laminas\View\Helper\Escape_Js;
use Laminas\View\Helper\Escape_Url;
use Laminas\View\Helper\Gravatar_Image;
use Laminas\View\Helper\Html_Attributes;
use Laminas\View\Helper\Html_List;
use Psr\Container\Container_Interface;
use function sprintf;
/**
 * This factory is used to generate helpers that have a single constructor argument on an Escaper instance
 *
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Escape_Helper_Factory implements Factory_Interface
{
    private const CAN_CREATE = [Escape_Css::class => Escape_Css::class, Escape_Html::class => Escape_Html::class, Escape_Html_Attr::class => Escape_Html_Attr::class, Escape_Js::class => Escape_Js::class, Escape_Url::class => Escape_Url::class, Html_Attributes::class => Html_Attributes::class, Html_List::class => Html_List::class, Gravatar_Image::class => Gravatar_Image::class];
    /** @inheritDoc */
    public function __invoke(Container_Interface $container, string $requested_name, ?array $options = null): mixed
    {
        $type = self::CAN_CREATE[$requested_name] ?? null;
        if ($type === null) {
            throw new InvalidArgumentException(sprintf('Dependencies of type "%s" cannot be created by this factory. ' . 'Only the following types are supported: %s', $requested_name, implode(', ', self::CAN_CREATE)));
        }
        $escaper = $container->has(Escaper_Interface::class) ? $container->get(Escaper_Interface::class) : new Escaper();
        return new $type($escaper);
    }
}