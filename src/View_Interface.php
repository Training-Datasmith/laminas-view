<?php

declare (strict_types=1);
namespace Laminas\View;

use Laminas\View\Exception\Rendering_Failed_Exception;
use Laminas\View\Model\Model_Interface;
interface View_Interface
{
    /**
     * Render a template
     *
     * Unless layout is disabled, the template will be "wrapped" in the configured layout
     *
     * @param non-empty-string|ModelInterface $modelOrTemplate
     * @param iterable<non-empty-string, mixed>|null|ModelInterface $variables
     * @throws RenderingFailedException When any exception occurs during render.
     */
    public function render(string|Model_Interface $model_or_template, iterable|Model_Interface|null $variables = null, bool $enable_layout = true): string;
    /**
     * Render a configured top-level layout view model
     *
     * It is expected that the given model will have a non-empty template configured and all necessary variables and
     * child models set.
     *
     * @throws RenderingFailedException When any exception occurs during render.
     */
    public function render_layout(Model_Interface $layout): string;
}