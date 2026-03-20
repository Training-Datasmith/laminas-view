<?php

declare (strict_types=1);
namespace Laminas\View\Renderer;

use function assert;
use Laminas\View\Exception\Rendering_Failed_Exception;
use Laminas\View\Helper\View_Model as ViewModelHelper;
use Laminas\View\Helper_Plugin_Manager_Interface;
use Laminas\View\Model\Model_Interface;
use Laminas\View\Model\View_Model;
use Laminas\View\Resolver\Resolver_Interface;
final class Php_Renderer implements Renderer_Interface
{
    /** @var (callable(string): string)|null */
    private $filter;
    public function __construct(private readonly Helper_Plugin_Manager_Interface $plugin_manager, private readonly Resolver_Interface $template_resolver, private readonly bool $strict_variables = true)
    {
    }
    /**
     * Set a post-rendering filter to apply to the rendered output
     *
     * @param callable(string): string $filter
     */
    public function set_filter(callable $filter): self
    {
        $this->filter = $filter;
        return $this;
    }
    /** @inheritDoc */
    public function render(string|Model_Interface $template_name_or_model, iterable|null $variables = null): string
    {
        $template_name = $template_name_or_model instanceof Model_Interface ? $template_name_or_model->get_template() : $template_name_or_model;
        if ($template_name === '') {
            throw Rendering_Failed_Exception::because_a_template_was_not_specified();
        }
        if ($template_name_or_model instanceof Model_Interface && $variables !== null) {
            throw Rendering_Failed_Exception::because_of_ambiguous_arguments_to_php_renderer();
        }
        $view_model = $template_name_or_model instanceof Model_Interface ? $template_name_or_model : new View_Model($variables ?? [], $template_name);
        $content = $this->render_model($view_model);
        if ($this->filter !== null) {
            return ($this->filter)($content);
        }
        return $content;
    }
    private function render_model(Model_Interface $model): string
    {
        $template = $model->get_template();
        assert($template !== '');
        $filename = $this->template_resolver->resolve($template);
        if ($filename === false) {
            throw Rendering_Failed_Exception::because_the_template_cannot_be_resolved_to_a_file($template);
        }
        $helper = $this->plugin_manager->get(View_Model_Helper::class);
        $helper->set_current($model);
        return (new Template($filename, $model->get_variables(), $this->plugin_manager, $this->strict_variables))();
    }
}