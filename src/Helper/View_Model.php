<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use Laminas\View\Model\Model_Interface;
/**
 * Helper for storing and retrieving the root and current view model
 */
final class View_Model implements Stateful_Helper_Interface
{
    private Model_Interface|null $current = null;
    private Model_Interface|null $root = null;
    public function reset_state(): void
    {
        $this->current = null;
        $this->root = null;
    }
    public function __invoke(): self
    {
        return $this;
    }
    /**
     * Set the current view model
     */
    public function set_current(Model_Interface $model): self
    {
        $this->current = $model;
        return $this;
    }
    /**
     * Get the current view model
     */
    public function get_current(): Model_Interface|null
    {
        return $this->current;
    }
    /**
     * Is a current view model composed?
     */
    public function has_current(): bool
    {
        return $this->current instanceof Model_Interface;
    }
    /**
     * Set the root view model
     */
    public function set_root(Model_Interface $model): self
    {
        $this->root = $model;
        return $this;
    }
    /**
     * Get the root view model
     */
    public function get_root(): Model_Interface|null
    {
        return $this->root;
    }
    /**
     * Is a root view model composed?
     */
    public function has_root(): bool
    {
        return $this->root instanceof Model_Interface;
    }
}