<?php

declare (strict_types=1);
namespace Laminas\View;

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Asset;
use Laminas\View\Helper\Base_Path;
use Laminas\View\Helper\Cycle;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\Escaper\Abstract_Helper;
use Laminas\View\Helper\Gravatar_Image;
use Laminas\View\Helper\Head_Link;
use Laminas\View\Helper\Head_Meta;
use Laminas\View\Helper\Head_Script;
use Laminas\View\Helper\Head_Style;
use Laminas\View\Helper\Head_Title;
use Laminas\View\Helper\Html_List;
use Laminas\View\Helper\Html_Object;
use Laminas\View\Helper\Html_Tag;
use Laminas\View\Helper\Inline_Script;
use Laminas\View\Helper\Layout;
use Laminas\View\Helper\Partial;
use Laminas\View\Helper\Partial_Loop;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\Placeholder\Position;
use Laminas\View\Helper\Render_Child_Model;
use Laminas\View\Helper\View_Model;
use Laminas\View\Model\Model_Interface;
use Stringable;
/**
 * This interface is used only for the purposes of auto-completion in your IDE
 *
 * You should not implement this interface, and as such, it will not retain backwards compatibility
 *
 * Feel free to write interfaces in your own projects that document helper signatures and extend from this interface.
 * It will be kept up-to-date with the shipped view helpers and their relevant signatures.
 *
 * @psalm-api
 * @psalm-import-type AttributeSet from HtmlAttributesSet
 */
interface Template_Interface
{
    /**
     * @see Asset
     *
     * @param non-empty-string $asset
     * @return non-empty-string
     * @throws Exception\InvalidArgumentException
     */
    public function asset(string $asset): string;
    /**
     * Returns site's base path, or file with base path prepended.
     *
     * $file is appended to the base path for simplicity.
     *
     * @see BasePath
     *
     * @throws RuntimeException
     */
    public function base_path(string|null $file = null): string;
    /**
     * Add elements to alternate
     *
     * @see Cycle
     *
     * @param list<scalar|Stringable> $data
     */
    public function cycle(array $data = [], string $name = 'default'): Cycle;
    /** @see Doctype */
    public function doctype(): Doctype;
    /**
     * @see AbstractHelper
     *
     * @param int-mask-of<AbstractHelper::RECURSE_*> $recurse Expects one of the recursion constants;
     *                                              used to decide whether to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function escape_css(mixed $value, int $recurse = Abstract_Helper::RECURSE_NONE): mixed;
    /**
     * @see AbstractHelper
     *
     * @param int-mask-of<AbstractHelper::RECURSE_*> $recurse Expects one of the recursion constants;
     *                                              used to decide whether to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function escape_html(mixed $value, int $recurse = Abstract_Helper::RECURSE_NONE): mixed;
    /**
     * @see AbstractHelper
     *
     * @param int-mask-of<AbstractHelper::RECURSE_*> $recurse Expects one of the recursion constants;
     *                                              used to decide whether to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function escape_html_attr(mixed $value, int $recurse = Abstract_Helper::RECURSE_NONE): mixed;
    /**
     * @see AbstractHelper
     *
     * @param int-mask-of<AbstractHelper::RECURSE_*> $recurse Expects one of the recursion constants;
     *                                              used to decide whether to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function escape_js(mixed $value, int $recurse = Abstract_Helper::RECURSE_NONE): mixed;
    /**
     * @see AbstractHelper
     *
     * @param int-mask-of<AbstractHelper::RECURSE_*> $recurse Expects one of the recursion constants;
     *                                              used to decide whether to recurse the given value when escaping
     * @throws Exception\InvalidArgumentException
     * @return mixed Given a scalar, a scalar value is returned. Given an object, with the $recurse flag not
     *               allowing object recursion, returns a string. Otherwise, returns an array.
     */
    public function escape_url(mixed $value, int $recurse = Abstract_Helper::RECURSE_NONE): mixed;
    /**
     * @see GravatarImage
     *
     * @param non-empty-string                                  $emailAddress
     * @param positive-int                                      $imageSize
     * @param AttributeSet                                      $imageAttributes
     * @param value-of<GravatarImage::DEFAULT_IMAGE_VALUES>|string $defaultImage
     * @param value-of<GravatarImage::RATINGS>                     $rating
     */
    public function gravatar_image(string $email_address, int $image_size = 80, array $image_attributes = [], string $default_image = Gravatar_Image::DEFAULT_MP, string $rating = Gravatar_Image::RATING_G): string;
    /**
     * @see HeadLink
     *
     * @param array<string, scalar>|null $attributes
     */
    public function head_link(array|null $attributes = null): Head_Link;
    /**
     * @see HeadMeta
     *
     * @param array<string, scalar> $attributes
     */
    public function head_meta(string|null $name = null, string|null $content = null, array $attributes = []): Head_Meta;
    /** @see HeadScript */
    public function head_script(): Head_Script;
    /**
     * @see HeadStyle
     *
     * Returns headStyle helper object; optionally, appends a new style element to the list
     *
     * @param string|null $content CSS to add to a style element
     * @param array<string, scalar> $attributes to apply to the style element
     */
    public function head_style(string|null $content = null, array $attributes = [], Position $position = Position::Append): Head_Style;
    /** @see HeadTitle */
    public function head_title(string|null $title = null): Head_Title;
    /**
     * Returns a new HtmlAttributesSet object, optionally initializing it with
     * the provided value.
     *
     * @param iterable<string, scalar|array|null> $attributes
     */
    public function html_attributes(iterable $attributes = []): Html_Attributes_Set;
    /**
     * Generates a 'List' element.
     *
     * @see HtmlList
     *
     * @param  array<array-key, scalar|array> $items Array with the elements of the list
     * @param  bool                           $ordered Specifies ordered/unordered list; default unordered
     * @param  AttributeSet|null              $attribs Attributes for the ol/ul tag.
     * @param  bool                           $escape Whether to Escape the items.
     * @throws Exception\InvalidArgumentException If $items is empty.
     * @return string The list XHTML.
     */
    public function html_list(array $items, bool $ordered = false, array|null $attribs = null, bool $escape = true): string;
    /**
     * Output an 'object'
     *
     * @see HtmlObject
     *
     * @param string $data The data attribute - the URL of the resource
     * @param string $type The mime type of the target resource
     * @param array<string, scalar> $attributes HTML tag attributes
     * @param array<string, scalar> $params Parameters for the resource
     * @param string|null $content Fallback content. This content is not escaped and is assumed to be markup.
     */
    public function html_object(string $data, string $type, array $attributes = [], array $params = [], string|null $content = null): string;
    /**
     * @see HtmlTag
     *
     * @param array<string, scalar> $attributes
     */
    public function __invoke(array $attributes = []): Html_Tag;
    /** @see InlineScript */
    public function inline_script(): Inline_Script;
    /**
     * Set layout template or retrieve "layout" view model
     *
     * If no arguments are given, grabs the "root" or "layout" view model.
     * Otherwise, attempts to set the template for that view model.
     *
     * @see Layout
     *
     * @param null|string $template Providing a template name will set that template as the current layout template
     * @return ($template is null ? ModelInterface : Layout)
     */
    public function layout(string|null $template = null): Model_Interface|Layout;
    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object. It proxies to view's render function
     *
     * @see Partial
     *
     * @param  string|ModelInterface|null $name Name of view script, or a view model
     * @param  iterable<string, mixed>|object|null $values Variables to populate in the view
     * @return ($name is null ? Partial : string)
     * @throws RuntimeException
     */
    public function partial(string|Model_Interface|null $name = null, iterable|object|null $values = null): string|Partial;
    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object.
     *
     * If no arguments are provided, returns object instance.
     *
     * @see PartialLoop
     *
     * @param string|null $name Name of view script
     * @param iterable|object $values Variables to populate in the view
     * @return ($name is string ? string : PartialLoop)
     * @throws Exception\InvalidArgumentException
     */
    public function partial_loop(string|null $name = null, iterable|object $values = []): Partial_Loop|string;
    /** @see Placeholder */
    public function placeholder(string|null $placeholder = null): Placeholder;
    /**
     * Render the child model identified by $child
     *
     * If a matching child model is found, it is rendered. If not, an empty string is returned.
     *
     * @see RenderChildModel
     *
     * @param non-empty-string $child
     */
    public function render_child_model(string $child): string;
    /** @see ViewModel */
    public function view_model(): View_Model;
    /** This method is here so that the custom template analyzer works */
    public function __internal_pseudo_render_for_psalm(): void;
}