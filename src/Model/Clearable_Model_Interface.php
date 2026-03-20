<?php

declare (strict_types=1);
namespace Laminas\View\Model;

/**
 * Interface describing methods for clearing the state of a view model.
 *
 * View models implementing this interface allow clearing children, options,
 * and variables.
 */
interface Clearable_Model_Interface
{
    public function clear_children(): static;
    public function clear_variables(): static;
}