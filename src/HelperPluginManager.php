<?php

declare (strict_types=1);
namespace Laminas\View;

use function array_replace_recursive;
use function get_debug_type;
use function is_callable;
use Laminas\Service_Manager\Abstract_Plugin_Manager;
use Laminas\Service_Manager\Exception\Invalid_Service_Exception;
use Laminas\Service_Manager\Factory\Invokable_Factory;
use Laminas\Service_Manager\Service_Manager;
use Laminas\View\Helper\Helper_Interface;
use Laminas\View\Helper\Service\Escape_Helper_Factory;
use Laminas\View\Helper\Service\Generic_Factory;
use Laminas\View\Helper\Stateful_Helper_Interface;
use Psr\Container\Container_Interface;
use function spl_object_id;
use function sprintf;
/**
 * Plugin manager implementation for view helpers
 *
 * Enforces that helpers retrieved are instances of HelperInterface, or callable.
 * Additionally, it registers a number of default helpers and tracks stateful helpers so that state can be reset.
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-type InstanceType = HelperInterface|callable
 * @extends AbstractPluginManager<InstanceType>
 */
final class Helper_Plugin_Manager extends Abstract_Plugin_Manager implements Helper_Plugin_Manager_Interface
{
    private const CONFIG = ['factories' => [Helper\Asset::class => Helper\Service\Asset_Factory::class, Helper\Base_Path::class => Helper\Service\Base_Path_Factory::class, Helper\Cycle::class => Invokable_Factory::class, Helper\Doctype::class => Helper\Service\Doctype_Factory::class, Helper\Escape_Css::class => Escape_Helper_Factory::class, Helper\Escape_Html::class => Escape_Helper_Factory::class, Helper\Escape_Html_Attr::class => Escape_Helper_Factory::class, Helper\Escape_Js::class => Escape_Helper_Factory::class, Helper\Escape_Url::class => Escape_Helper_Factory::class, Helper\Gravatar_Image::class => Escape_Helper_Factory::class, Helper\Head_Link::class => Generic_Factory::class, Helper\Head_Meta::class => Generic_Factory::class, Helper\Head_Script::class => Generic_Factory::class, Helper\Head_Style::class => Generic_Factory::class, Helper\Head_Title::class => Helper\Service\Head_Title_Factory::class, Helper\Html_Attributes::class => Escape_Helper_Factory::class, Helper\Html_List::class => Escape_Helper_Factory::class, Helper\Html_Object::class => Generic_Factory::class, Helper\Html_Tag::class => Generic_Factory::class, Helper\Inline_Script::class => Generic_Factory::class, Helper\Layout::class => Helper\Service\Layout_Factory::class, Helper\Partial_Loop::class => Helper\Service\Partial_Loop_Factory::class, Helper\Partial::class => Helper\Service\Partial_Factory::class, Helper\Placeholder::class => Invokable_Factory::class, Helper\Render_To_Placeholder::class => Helper\Service\Render_To_Placeholder_Factory::class, Helper\View_Model::class => Invokable_Factory::class], 'aliases' => [
        'asset' => Helper\Asset::class,
        'Asset' => Helper\Asset::class,
        'basePath' => Helper\Base_Path::class,
        'BasePath' => Helper\Base_Path::class,
        'basepath' => Helper\Base_Path::class,
        'Cycle' => Helper\Cycle::class,
        'cycle' => Helper\Cycle::class,
        'Doctype' => Helper\Doctype::class,
        'doctype' => Helper\Doctype::class,
        // overridden by a factory in ViewHelperManagerFactory
        'escapeCss' => Helper\Escape_Css::class,
        'EscapeCss' => Helper\Escape_Css::class,
        'escapecss' => Helper\Escape_Css::class,
        'escapeHtmlAttr' => Helper\Escape_Html_Attr::class,
        'EscapeHtmlAttr' => Helper\Escape_Html_Attr::class,
        'escapehtmlattr' => Helper\Escape_Html_Attr::class,
        'escapeHtml' => Helper\Escape_Html::class,
        'EscapeHtml' => Helper\Escape_Html::class,
        'escapehtml' => Helper\Escape_Html::class,
        'escapeJs' => Helper\Escape_Js::class,
        'EscapeJs' => Helper\Escape_Js::class,
        'escapejs' => Helper\Escape_Js::class,
        'escapeUrl' => Helper\Escape_Url::class,
        'EscapeUrl' => Helper\Escape_Url::class,
        'escapeurl' => Helper\Escape_Url::class,
        'gravatarImage' => Helper\Gravatar_Image::class,
        'headLink' => Helper\Head_Link::class,
        'HeadLink' => Helper\Head_Link::class,
        'headlink' => Helper\Head_Link::class,
        'headMeta' => Helper\Head_Meta::class,
        'HeadMeta' => Helper\Head_Meta::class,
        'headmeta' => Helper\Head_Meta::class,
        'headScript' => Helper\Head_Script::class,
        'HeadScript' => Helper\Head_Script::class,
        'headscript' => Helper\Head_Script::class,
        'headStyle' => Helper\Head_Style::class,
        'HeadStyle' => Helper\Head_Style::class,
        'headstyle' => Helper\Head_Style::class,
        'headTitle' => Helper\Head_Title::class,
        'HeadTitle' => Helper\Head_Title::class,
        'headtitle' => Helper\Head_Title::class,
        'htmlattributes' => Helper\Html_Attributes::class,
        'htmlAttributes' => Helper\Html_Attributes::class,
        'HtmlAttributes' => Helper\Html_Attributes::class,
        'htmllist' => Helper\Html_List::class,
        'htmlList' => Helper\Html_List::class,
        'HtmlList' => Helper\Html_List::class,
        'htmlobject' => Helper\Html_Object::class,
        'htmlObject' => Helper\Html_Object::class,
        'HtmlObject' => Helper\Html_Object::class,
        'htmltag' => Helper\Html_Tag::class,
        'htmlTag' => Helper\Html_Tag::class,
        'HtmlTag' => Helper\Html_Tag::class,
        'inlinescript' => Helper\Inline_Script::class,
        'inlineScript' => Helper\Inline_Script::class,
        'InlineScript' => Helper\Inline_Script::class,
        'layout' => Helper\Layout::class,
        'Layout' => Helper\Layout::class,
        'partial' => Helper\Partial::class,
        'partialloop' => Helper\Partial_Loop::class,
        'partialLoop' => Helper\Partial_Loop::class,
        'PartialLoop' => Helper\Partial_Loop::class,
        'Partial' => Helper\Partial::class,
        'placeholder' => Helper\Placeholder::class,
        'Placeholder' => Helper\Placeholder::class,
        'rendertoplaceholder' => Helper\Render_To_Placeholder::class,
        'renderToPlaceholder' => Helper\Render_To_Placeholder::class,
        'RenderToPlaceholder' => Helper\Render_To_Placeholder::class,
        'view_model' => Helper\View_Model::class,
        'viewmodel' => Helper\View_Model::class,
        'viewModel' => Helper\View_Model::class,
        'ViewModel' => Helper\View_Model::class,
    ]];
    /**
     * A hash map of `spl_object_id` to Helper instance
     *
     * @var array<int, StatefulHelperInterface>
     */
    private array $stateful_helpers = [];
    /**
     * Constructor
     *
     * Merges provided configuration with default configuration.
     *
     * @inheritDoc
     */
    public function __construct(Container_Interface $creation_context, array $config = [])
    {
        /** @psalm-var ServiceManagerConfiguration $config Psalm cannot infer this after merge */
        $config = array_replace_recursive(self::CONFIG, $config);
        parent::__construct($creation_context, $config);
    }
    /**
     * Validate the plugin is of the expected type.
     *
     * Validates against callables and HelperInterface implementations.
     *
     * @throws InvalidServiceException
     * @psalm-assert HelperInterface|callable $instance
     */
    public function validate(mixed $instance): void
    {
        if (!is_callable($instance) && !$instance instanceof Helper_Interface) {
            throw new Invalid_Service_Exception(sprintf('%s can only create instances of %s and/or callables; %s is invalid', self::class, Helper_Interface::class, get_debug_type($instance)));
        }
    }
    /**
     * @template InstanceParam of HelperInterface
     * @param class-string<InstanceParam>|string $id Service name of plugin to retrieve.
     * @return ($id is class-string<InstanceParam> ? InstanceParam : InstanceType)
     */
    public function get(string $id): mixed
    {
        /** @psalm-var InstanceType $plugin Unfortunately this type needs forcing */
        $plugin = parent::get($id);
        if ($plugin instanceof Stateful_Helper_Interface) {
            $this->stateful_helpers[spl_object_id($plugin)] = $plugin;
        }
        return $plugin;
    }
    public function reset_state(): void
    {
        foreach ($this->stateful_helpers as $helper) {
            $helper->reset_state();
        }
        $this->stateful_helpers = [];
    }
}