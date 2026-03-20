<?php

declare (strict_types=1);
namespace Laminas\View;

use Closure;
use function is_string;
use Laminas\View\Exception\Rendering_Failed_Exception;
use Laminas\View\Helper\View_Model as ViewModelHelper;
use Laminas\View\Model\Model_Interface;
use Laminas\View\Model\View_Model;
use Laminas\View\Renderer\Renderer_Interface;
final class View implements View_Interface
{
    private readonly View_Model_Helper $view_model_helper;
    /** @var list<(Closure(ModelInterface): ModelInterface)> */
    private array $pre_render_handlers;
    /**
     * @param non-empty-string $defaultLayoutTemplate
     * @param non-empty-string $defaultCaptureTo
     */
    public function __construct(private readonly Renderer_Interface $renderer, private readonly Helper_Plugin_Manager_Interface $plugin_manager, private readonly string $default_layout_template, private readonly string $default_capture_to)
    {
        $this->view_model_helper = $this->plugin_manager->get(View_Model_Helper::class);
        $this->pre_render_handlers = [];
    }
    /** @inheritDoc */
    public function render_layout(Model_Interface $layout): string
    {
        $this->view_model_helper->set_root($layout);
        $content = $this->render_recursively($layout);
        $this->plugin_manager->reset_state();
        return $content;
    }
    /** @inheritDoc */
    public function render(string|Model_Interface $model_or_template, iterable|Model_Interface|null $variables = null, bool $enable_layout = true): string
    {
        if (is_string($model_or_template)) {
            $model = $variables instanceof Model_Interface ? $variables : new View_Model($variables ?? []);
            $model->set_template($model_or_template);
        } else {
            $model = $model_or_template;
        }
        if ($enable_layout && $model->terminate() !== true) {
            $layout_model = new View_Model([]);
            $layout_model->set_template($this->default_layout_template);
            $model->set_capture_to($this->default_capture_to);
            $layout_model->add_child($model);
            return $this->render_layout($layout_model);
        }
        $content = $this->render_recursively($model);
        $this->plugin_manager->reset_state();
        return $content;
    }
    /** @throws RenderingFailedException When any exception occurs during render. */
    private function render_recursively(Model_Interface $model): string
    {
        foreach ($model->get_children() as $child) {
            $this->view_model_helper->set_current($child);
            $content = $this->render_recursively($child);
            if ($child->is_append()) {
                /** @psalm-var mixed $existingContent */
                $existing_content = $model->get_variable($child->capture_to(), '');
                $existing_content = is_string($existing_content) ? $existing_content : '';
                $content = $existing_content . $content;
            }
            $model->set_variable($child->capture_to(), $content);
        }
        $this->view_model_helper->set_current($model);
        return $this->renderer->render($this->before_render($model));
    }
    /** @param Closure(ModelInterface): ModelInterface $handler */
    public function register_pre_render_handler(Closure $handler): void
    {
        $this->pre_render_handlers[] = $handler;
    }
    private function before_render(Model_Interface $model): Model_Interface
    {
        foreach ($this->pre_render_handlers as $handler) {
            $model = $handler($model);
        }
        return $model;
    }
}