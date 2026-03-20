<?php

declare (strict_types=1);
namespace Laminas\View;

use Laminas\Service_Manager\Plugin_Manager_Interface;
use Laminas\View\Helper\Helper_Interface;
/** @extends PluginManagerInterface<HelperInterface|callable> */
interface Helper_Plugin_Manager_Interface extends Plugin_Manager_Interface
{
    /**
     * Resets the internal state built up in any view helpers so that further rendering cycles are not polluted
     */
    public function reset_state(): void;
}