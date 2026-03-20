<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use const E_USER_DEPRECATED;
use function get_object_vars;
use function is_array;
use function iterator_to_array;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Model\Model_Interface;
use Laminas\View\Renderer\Php_Renderer;
use function method_exists;
use Traversable;
use function trigger_error;
/**
 * Helper for rendering a template fragment in its own variable scope.
 */
final class Partial implements Stateful_Helper_Interface
{
    /**
     * Variable to which object will be assigned
     *
     * @var non-empty-string|null
     */
    private string|null $object_key = null;
    public function __construct(private readonly Php_Renderer $renderer)
    {
    }
    public function reset_state(): void
    {
        $this->object_key = null;
    }
    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object. It proxies to view's render function
     *
     * @param  non-empty-string|ModelInterface|null $name Name of view script, or a view model
     * @param  iterable<non-empty-string, mixed>|object|null $values Variables to populate in the view
     * @return ($name is null ? self : string)
     * @throws RuntimeException
     */
    public function __invoke(string|Model_Interface|null $name = null, iterable|object|null $values = null): string|self
    {
        if ($name === null) {
            return $this;
        }
        // If we were passed only a view model, just render it.
        if ($name instanceof Model_Interface) {
            return $this->renderer->render($name);
        }
        return $this->renderer->render($name, $this->extract_variables_for_render($values));
    }
    /**
     * @param iterable<non-empty-string, mixed>|object|null $values
     * @return iterable<non-empty-string, mixed>
     */
    private function extract_variables_for_render(iterable|object|null $values): iterable
    {
        if ($values === null) {
            return [];
        }
        if (is_array($values)) {
            return $values;
        }
        if ($values instanceof Model_Interface) {
            return $values->get_variables();
        }
        if ($this->object_key !== null) {
            return [$this->object_key => $values];
        }
        if ($values instanceof Traversable) {
            return iterator_to_array($values);
        }
        if (method_exists($values, 'toArray')) {
            trigger_error('Non-iterable objects implementing a `toArray` method will be rejected in version 4.0 ' . 'of laminas-view ', E_USER_DEPRECATED);
            /** @psalm-var mixed $variables */
            $variables = $values->to_array();
            if (is_array($variables)) {
                return $variables;
                // We cannot guarantee iterable<non-empty-string, mixed> here
            }
        }
        /** @psalm-var array<non-empty-string, mixed> */
        return get_object_vars($values);
    }
    /**
     * Set object key
     *
     * @param non-empty-string|null $key
     */
    public function set_object_key(string|null $key): self
    {
        $this->object_key = $key;
        return $this;
    }
    /**
     * Retrieve object key
     *
     * The objectKey is the variable to which an object in the iterator will be
     * assigned.
     *
     * @return non-empty-string|null
     */
    public function get_object_key(): string|null
    {
        return $this->object_key;
    }
}