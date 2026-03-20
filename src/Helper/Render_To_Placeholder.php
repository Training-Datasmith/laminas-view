<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use Laminas\View\Model\Model_Interface;
use Laminas\View\Renderer\Php_Renderer;
/**
 * Renders a template and stores the rendered output as a placeholder
 * variable for later use.
 */
final readonly class Render_To_Placeholder
{
    public function __construct(private Php_Renderer $renderer, private Placeholder $placeholder)
    {
    }
    /**
     * Renders a template and stores the rendered output as a placeholder
     * variable for later use.
     *
     * @param non-empty-string|ModelInterface $script The template script to render
     * @param non-empty-string $placeholder The placeholder variable name in which to store the output
     */
    public function __invoke(string|Model_Interface $script, string $placeholder): void
    {
        $this->placeholder->__invoke()->append($this->renderer->render($script), $placeholder);
    }
}