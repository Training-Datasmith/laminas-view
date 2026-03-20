<?php

declare (strict_types=1);
namespace Laminas\View\Helper\Service;

use function implode;
use Laminas\Escaper\Escaper;
use Laminas\Escaper\Escaper_Interface;
use Laminas\Service_Manager\Factory\Factory_Interface;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\Head_Link;
use Laminas\View\Helper\Head_Meta;
use Laminas\View\Helper\Head_Script;
use Laminas\View\Helper\Head_Style;
use Laminas\View\Helper\Html_Object;
use Laminas\View\Helper\Html_Tag;
use Laminas\View\Helper\Inline_Script;
use Laminas\View\Helper_Plugin_Manager;
use Psr\Container\Container_Interface;
use function sprintf;
/**
 * This factory is used to initialise helpers that have common constructor dependencies
 *
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Generic_Factory implements Factory_Interface
{
    private const CAN_CREATE = [Head_Link::class => Head_Link::class, Head_Meta::class => Head_Meta::class, Head_Script::class => Head_Script::class, Head_Style::class => Head_Style::class, Html_Object::class => Html_Object::class, Html_Tag::class => Html_Tag::class, Inline_Script::class => Inline_Script::class];
    /** @inheritDoc */
    public function __invoke(Container_Interface $container, string $requested_name, ?array $options = null): mixed
    {
        $type = self::CAN_CREATE[$requested_name] ?? null;
        if ($type === null) {
            throw new InvalidArgumentException(sprintf('Dependencies of type "%s" cannot be created by this factory. ' . 'Only the following types are supported: %s', $requested_name, implode(', ', self::CAN_CREATE)));
        }
        $escaper = $container->has(Escaper_Interface::class) ? $container->get(Escaper_Interface::class) : new Escaper();
        $helpers = $container->get(Helper_Plugin_Manager::class);
        return new $type($escaper, $helpers->get(Doctype::class));
    }
}