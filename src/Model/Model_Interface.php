<?php

declare (strict_types=1);
namespace Laminas\View\Model;

use Countable;
use IteratorAggregate;
/**
 * Interface describing a view model.
 *
 * Apart from being a container for view variables, view models also aggregate "child" or nested models.
 * View models allow iteration over child models and they are also countable, yielding the number of child models
 * attached.
 *
 * @extends IteratorAggregate<int, ModelInterface>
 */
interface Model_Interface extends Countable, IteratorAggregate
{
    /**
     * Get a single view variable
     *
     * @param non-empty-string $name
     */
    public function get_variable(string $name, mixed $default = null): mixed;
    /**
     * Set view variable
     *
     * @param non-empty-string $name
     */
    public function set_variable(string $name, mixed $value): static;
    /**
     * Set view variables en masse
     *
     * @param iterable<non-empty-string, mixed> $variables
     */
    public function set_variables(iterable $variables, bool $overwrite = false): static;
    /**
     * Get view variables
     *
     * @return array<non-empty-string, mixed>
     */
    public function get_variables(): array;
    /**
     * Set the template to be used by this model
     *
     * @param non-empty-string $template
     */
    public function set_template(string $template): static;
    /**
     * Get the template to be used by this model
     *
     * Implementations should return an empty string when the template has not been set
     */
    public function get_template(): string;
    /**
     * Add a child model
     *
     * @param null|non-empty-string $captureTo Optional; if specified, the "capture to" value to set on the child.
     *                                         When null, the default 'capture to' value is used.
     * @param bool|null $append Optional; when true, the child model will be marked as an appending model.
     */
    public function add_child(Model_Interface $child, string|null $capture_to = null, bool|null $append = null): static;
    /**
     * Return all children.
     *
     * @return list<ModelInterface>
     */
    public function get_children(): array;
    /**
     * Does the model have any children?
     */
    public function has_children(): bool;
    /**
     * Set the name of the variable to capture this model to, if it is a child model
     *
     * @param non-empty-string $capture
     */
    public function set_capture_to(string $capture): static;
    /**
     * Get the name of the variable to which to capture this model
     *
     * @return non-empty-string
     */
    public function capture_to(): string;
    /**
     * Set flag indicating whether this is considered a terminal or standalone model
     */
    public function set_terminal(bool $terminate): static;
    /**
     * Is this considered a terminal or standalone model?
     */
    public function terminate(): bool;
    /**
     * Set flag indicating whether to append to child with the same capture
     */
    public function set_append(bool $append): static;
    /**
     * Is this append to child  with the same capture?
     */
    public function is_append(): bool;
}