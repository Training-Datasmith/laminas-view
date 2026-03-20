<?php

declare (strict_types=1);
namespace Laminas\View\Model;

use function array_key_exists;
use function array_map;
use function array_merge;
use ArrayIterator;
use function count;
use function is_array;
use function iterator_to_array;
use Laminas\View\Exception\InvalidArgumentException;
use Traversable;
/**
 * @psalm-no-seal-properties This object is a mixed property bag
 */
final class View_Model implements Model_Interface, Clearable_Model_Interface, Retrievable_Children_Interface
{
    /**
     * What variable a parent model should capture this model to
     *
     * @var non-empty-string
     */
    private string $capture_to = 'content';
    /**
     * Child models
     *
     * @var list<ModelInterface>
     */
    private array $children = [];
    /**
     * Is this a standalone, or terminal, model?
     */
    private bool $terminate = false;
    /**
     * View variables
     *
     * @var array<non-empty-string, mixed>
     */
    private array $variables;
    /**
     * Is this append to child with the same capture?
     */
    private bool $append = false;
    /**
     * @param iterable<non-empty-string, mixed> $variables
     * @param array<non-empty-string, ModelInterface> $children
     */
    public function __construct(iterable $variables = [], private string $template = '', array $children = [])
    {
        $this->variables = array_map(static fn(mixed $value): mixed => $value, is_array($variables) ? $variables : iterator_to_array($variables));
        foreach ($children as $capture_to => $child) {
            $this->add_child($child, $capture_to);
        }
    }
    /**
     * Property overloading: set variable value
     *
     * @param non-empty-string $name
     */
    public function __set(string $name, mixed $value): void
    {
        $this->variables[$name] = $value;
    }
    /**
     * Property overloading: get variable value
     *
     * @param non-empty-string $name
     */
    public function __get(string $name): mixed
    {
        return $this->variables[$name] ?? null;
    }
    /**
     * Property overloading: do we have the requested variable value?
     *
     * @param non-empty-string $name
     */
    public function __isset(string $name): bool
    {
        return isset($this->variables[$name]);
    }
    /**
     * Property overloading: unset the requested variable
     *
     * @param non-empty-string $name
     */
    public function __unset(string $name): void
    {
        unset($this->variables[$name]);
    }
    /** @inheritDoc */
    public function get_variable(string $name, mixed $default = null): mixed
    {
        return array_key_exists($name, $this->variables) ? $this->variables[$name] : $default;
    }
    /** @inheritDoc */
    public function set_variable(string $name, mixed $value): static
    {
        $this->{$name} = $value;
        return $this;
    }
    /** @inheritDoc */
    public function set_variables(iterable $variables, bool $overwrite = false): static
    {
        if ($overwrite) {
            $this->variables = [];
        }
        $this->variables = array_merge($this->variables, array_map(static fn(mixed $value): mixed => $value, is_array($variables) ? $variables : iterator_to_array($variables)));
        return $this;
    }
    /** @inheritDoc */
    public function get_variables(): array
    {
        return $this->variables;
    }
    public function clear_variables(): static
    {
        $this->variables = [];
        return $this;
    }
    public function set_template(string $template): static
    {
        $this->template = $template;
        return $this;
    }
    public function get_template(): string
    {
        return $this->template;
    }
    public function add_child(Model_Interface $child, string|null $capture_to = null, bool|null $append = null): static
    {
        if ($capture_to !== null) {
            $child = $child->set_capture_to($capture_to);
        }
        if ($append !== null) {
            $child = $child->set_append($append);
        }
        $this->children[] = $child;
        return $this;
    }
    public function get_children(): array
    {
        return $this->children;
    }
    public function has_children(): bool
    {
        return $this->children !== [];
    }
    public function clear_children(): static
    {
        $this->children = [];
        return $this;
    }
    public function get_children_by_capture_to(string $capture, bool $recursive = true): array
    {
        $children = [];
        foreach ($this->children as $child) {
            if ($recursive === true && $child instanceof Retrievable_Children_Interface) {
                $children = array_merge($children, $child->get_children_by_capture_to($capture));
            }
            if ($child->capture_to() === $capture) {
                $children[] = $child;
            }
        }
        return $children;
    }
    public function set_capture_to(string $capture): static
    {
        /** @psalm-suppress TypeDoesNotContainType Adding a defensive check here regardless of documented types */
        if ($capture === '') {
            throw new InvalidArgumentException('The `capture` target cannot be an empty string');
        }
        $this->capture_to = $capture;
        return $this;
    }
    public function capture_to(): string
    {
        return $this->capture_to;
    }
    public function set_terminal(bool $terminate): static
    {
        $this->terminate = $terminate;
        return $this;
    }
    public function terminate(): bool
    {
        return $this->terminate;
    }
    public function set_append(bool $append): static
    {
        $this->append = $append;
        return $this;
    }
    public function is_append(): bool
    {
        return $this->append;
    }
    public function count(): int
    {
        return count($this->children);
    }
    /**
     * Get iterator of children
     *
     * @return Traversable<int, ModelInterface>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->children);
    }
}