<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Model\Model_Interface;
use function sprintf;
/**
 * View helper for changing the layout template or retrieving the layout (root) view model
 */
final readonly class Layout
{
    public function __construct(private View_Model $view_model_helper)
    {
    }
    /**
     * Set layout template or retrieve "layout" view model
     *
     * If no arguments are given, grabs the "root" or "layout" view model.
     * Otherwise, attempts to set the template for that view model.
     *
     * @param null|non-empty-string $template Provide a template name to set that template as the current layout
     * @return ($template is null ? ModelInterface : self)
     */
    public function __invoke(string|null $template = null): Model_Interface|self
    {
        $root_model = $this->get_root();
        if (null === $template) {
            return $root_model;
        }
        $root_model->set_template($template);
        return $this;
    }
    /**
     * Get the root view model
     *
     * @throws RuntimeException
     */
    private function get_root(): Model_Interface
    {
        $root = $this->view_model_helper->get_root();
        if (!$root instanceof Model_Interface) {
            throw new RuntimeException(sprintf('%s: no view model currently registered as root in renderer', __METHOD__));
        }
        return $root;
    }
}