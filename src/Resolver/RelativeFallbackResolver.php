<?php

declare (strict_types=1);
namespace Laminas\View\Resolver;

use Laminas\View\Helper\View_Model;
use Laminas\View\Model\Model_Interface;
use function strrpos;
use function substr;
/**
 * Relative fallback resolver - resolves to view templates in a sub-path of the
 * currently set view model's template.
 *
 * The current view model is retrieved from the ViewModel view helper
 *
 * This allows for usage of partial template paths such as `some/partial`, resolving to
 * `my/module/script/path/some/partial.phtml`, while rendering template `my/module/script/path/my-view`
 */
final readonly class Relative_Fallback_Resolver implements Resolver_Interface
{
    public const NS_SEPARATOR = '/';
    public function __construct(private Resolver_Interface $resolver, private View_Model $view_model_helper)
    {
    }
    /** @inheritDoc */
    public function resolve(string $name): string|false
    {
        $template = $this->resolve_template_name($name);
        if ($template === false) {
            return false;
        }
        return $this->resolver->resolve($template);
    }
    /**
     * @param non-empty-string $name
     * @return non-empty-string|false
     */
    private function resolve_template_name(string $name): string|false
    {
        $current_model = $this->view_model_helper->get_current();
        if (!$current_model instanceof Model_Interface) {
            return false;
        }
        $current_template = $current_model->get_template();
        $position = strrpos($current_template, self::NS_SEPARATOR);
        if ($position === false) {
            return false;
        }
        return substr($current_template, 0, $position) . self::NS_SEPARATOR . $name;
    }
}