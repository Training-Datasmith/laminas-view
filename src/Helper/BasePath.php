<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use Laminas\View\Exception\RuntimeException;
use function ltrim;
use function rtrim;
use function sprintf;
/**
 * Helper for retrieving the base path.
 */
final class Base_Path implements Stateful_Helper_Interface
{
    private string|null $base_path;
    private readonly string|null $configured_base_path;
    public function __construct(string|null $base_path = null)
    {
        if ($base_path !== null) {
            $base_path = rtrim($base_path, '/');
        }
        $this->base_path = $base_path;
        $this->configured_base_path = $base_path;
    }
    public function reset_state(): void
    {
        $this->base_path = $this->configured_base_path;
    }
    /**
     * Returns site's base path, or file with base path prepended.
     *
     * $file is appended to the base path for simplicity.
     *
     * @throws RuntimeException
     */
    public function __invoke(string|null $file = null): string
    {
        if ($this->base_path === null) {
            throw new RuntimeException('No base path provided');
        }
        if ($file !== null && $file !== '') {
            return sprintf('%s/%s', $this->base_path, ltrim($file, '/'));
        }
        return $this->base_path;
    }
    /**
     * Set the base path.
     */
    public function set_base_path(string $base_path): self
    {
        $this->base_path = rtrim($base_path, '/');
        return $this;
    }
}