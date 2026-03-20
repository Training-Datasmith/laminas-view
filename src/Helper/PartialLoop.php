<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use const E_USER_DEPRECATED;
use function get_debug_type;
use function is_array;
use function iterator_to_array;
use Laminas\View\Exception;
use function method_exists;
use function sprintf;
use Traversable;
use function trigger_error;
/**
 * Helper for rendering a template fragment in its own variable scope; iterates
 * over data provided and renders for each iteration.
 */
final class Partial_Loop implements Stateful_Helper_Interface
{
    /**
     * Marker to where the pointer is at in the loop
     */
    private int $partial_counter = 0;
    /**
     * The current nesting level
     */
    private int $nesting_level = 0;
    /**
     * Stack with object keys for each nested level
     *
     * @var array<int, non-empty-string|null> indexed by nesting level
     */
    private array $object_key_stack = [0 => null];
    public function __construct(private readonly Partial $partial_helper)
    {
    }
    public function reset_state(): void
    {
        $this->partial_helper->reset_state();
        $this->partial_counter = 0;
        $this->nesting_level = 0;
        $this->object_key_stack = [0 => null];
    }
    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object.
     *
     * If no arguments are provided, returns object instance.
     *
     * @param non-empty-string|null $name Name of view script
     * @param iterable|object $values Variables to populate in the view
     * @return ($name is string ? string : self)
     * @throws Exception\InvalidArgumentException
     */
    public function __invoke(string|null $name = null, iterable|object $values = []): self|string
    {
        if ($name === null) {
            return $this;
        }
        return $this->loop($name, $values);
    }
    /**
     * Renders a template fragment within a variable scope distinct from the
     * calling View object.
     *
     * @param non-empty-string $name Name of view script
     * @param iterable|object $values Variables to populate in the view
     * @throws Exception\InvalidArgumentException
     */
    private function loop(string $name, iterable|object $values): string
    {
        // reset the counter if it's called again
        $this->partial_counter = 0;
        $content = '';
        /** @psalm-var mixed $item */
        foreach ($this->extract_view_variables($values) as $item) {
            $this->nest_object_key();
            $this->partial_counter++;
            $content .= $this->partial_helper->__invoke($name, $item);
            $this->un_nest_object_key();
        }
        return $content;
    }
    /**
     * Get the partial counter
     */
    public function get_partial_counter(): int
    {
        return $this->partial_counter;
    }
    /**
     * Set object key in this loop and any child loop
     *
     * @param non-empty-string|null $key
     */
    public function set_object_key(string|null $key): self
    {
        if (null === $key) {
            unset($this->object_key_stack[$this->nesting_level]);
        } else {
            $this->object_key_stack[$this->nesting_level] = $key;
        }
        $this->partial_helper->set_object_key($key);
        return $this;
    }
    /** @return non-empty-string|null */
    public function get_object_key(): string|null
    {
        return $this->partial_helper->get_object_key();
    }
    /**
     * Increment nestedLevel and default objectKey to parent's value
     */
    private function nest_object_key(): void
    {
        $this->nesting_level += 1;
        $this->set_object_key($this->get_object_key());
    }
    /**
     * Decrement nestedLevel and restore objectKey to parent's value
     */
    private function un_nest_object_key(): void
    {
        $this->set_object_key(null);
        $this->nesting_level -= 1;
        if (isset($this->object_key_stack[$this->nesting_level])) {
            $this->partial_helper->set_object_key($this->object_key_stack[$this->nesting_level]);
        }
    }
    /**
     * @return array<array-key, mixed> Variables to populate in the view
     */
    private function extract_view_variables(iterable|object $values): array
    {
        if (is_array($values)) {
            return $values;
        }
        if ($values instanceof Traversable) {
            return iterator_to_array($values);
        }
        if (method_exists($values, 'toArray')) {
            trigger_error('Non-iterable objects implementing a `toArray` method will be rejected in version 4.0 ' . 'of laminas-view ', E_USER_DEPRECATED);
            /** @psalm-var mixed $data */
            $data = $values->to_array();
            if (is_array($data)) {
                return $data;
            }
        }
        throw new Exception\InvalidArgumentException(sprintf('PartialLoop helper requires iterable data, %s given', get_debug_type($values)));
    }
}