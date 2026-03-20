<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

/**
 * This interface defines a view helper that maintains state and provides a way to reset that state
 */
interface Stateful_Helper_Interface
{
    public function reset_state(): void;
}