<?php

declare (strict_types=1);
namespace Laminas\View\Renderer;

use Laminas\View\Exception\Rendering_Failed_Exception;
use Laminas\View\Model\Model_Interface;
/**
 * Interface class for Laminas\View\Renderer\* compatible template engine implementations
 */
interface Renderer_Interface
{
    /**
     * Processes a view script and returns the output.
     *
     * @param non-empty-string|ModelInterface $templateNameOrModel Either the name of a template to render (Not a path)
     *                                                             or a view model (Referencing a template name)
     * @param iterable<non-empty-string, mixed>|null $variables    Variables to use during rendering, if a model is not
     *                                                             passed as the first argument.
     * @return string The rendered output
     * @throws RenderingFailedException When any issue occurs during rendering.
     */
    public function render(string|Model_Interface $template_name_or_model, iterable|null $variables = null): string;
}