<?php

declare (strict_types=1);
namespace Laminas\View\Model;

/**
 * Interface describing a Retrievable Child Model
 *
 * Models implementing this interface provide a way to get their children by capture
 */
interface Retrievable_Children_Interface
{
    /**
     * Returns an array of View models with captureTo value $capture
     *
     * @param bool $recursive search recursive through children, default true
     * @return list<ModelInterface>
     */
    public function get_children_by_capture_to(string $capture, bool $recursive = true): array;
}