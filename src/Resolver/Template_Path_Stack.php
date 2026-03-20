<?php

declare (strict_types=1);
namespace Laminas\View\Resolver;

use function assert;
use function count;
use const DIRECTORY_SEPARATOR;
use function file_exists;
use function is_array;
use Laminas\Stdlib\SplStack;
use Laminas\View\Exception;
use Laminas\View\Exception\DomainException;
use function ltrim;
use function pathinfo;
use const PATHINFO_EXTENSION;
use function preg_match;
use function rtrim;
use Spl_File_Info;
use function str_starts_with;
/**
 * Resolves view scripts based on a stack of paths
 *
 * @psalm-type PathStack = SplStack<non-empty-string>
 * @psalm-type Options = array{
 *     lfi_protection?: bool,
 *     script_paths?: list<non-empty-string>,
 *     default_suffix?: non-empty-string,
 * }
 */
final class Template_Path_Stack implements Resolver_Interface
{
    /**
     * Default suffix to use
     *
     * Appends this suffix if the template requested does not use it.
     *
     * @var non-empty-string
     */
    private readonly string $default_suffix;
    /** @var PathStack */
    private SplStack $paths;
    /**
     * Flag indicating whether LFI protection for rendering view scripts is enabled
     */
    private readonly bool $lfi_protection_on;
    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $suffix = ltrim($options['default_suffix'] ?? 'phtml', '.');
        assert($suffix !== '');
        $this->default_suffix = $suffix;
        $this->lfi_protection_on = $options['lfi_protection'] ?? true;
        /** @psalm-var PathStack $paths */
        $paths = new SplStack();
        $this->paths = $paths;
        $add_paths = $options['script_paths'] ?? null;
        if (is_array($add_paths)) {
            $this->add_paths($add_paths);
        }
    }
    /**
     * Add many paths to the stack at once
     *
     * @param  list<non-empty-string> $paths
     * @return $this
     */
    public function add_paths(array $paths): self
    {
        foreach ($paths as $path) {
            $this->add_path($path);
        }
        return $this;
    }
    /**
     * Reset the path stack to the paths provided
     *
     * @param list<non-empty-string> $paths
     * @throws Exception\InvalidArgumentException
     */
    public function set_paths(array $paths): self
    {
        $this->clear_paths();
        $this->add_paths($paths);
        return $this;
    }
    /**
     * Normalize a path for insertion in the stack
     *
     * @return non-empty-string
     */
    private static function normalize_path(string $path): string
    {
        $path = rtrim($path, '/\\');
        return $path . DIRECTORY_SEPARATOR;
    }
    /**
     * Add a single path to the stack
     *
     * @param non-empty-string $path
     * @return $this
     */
    public function add_path(string $path): self
    {
        $this->paths->push(self::normalize_path($path));
        return $this;
    }
    /**
     * Clear all paths
     */
    public function clear_paths(): void
    {
        /** @psalm-var PathStack $paths */
        $paths = new SplStack();
        $this->paths = $paths;
    }
    /**
     * Returns stack of paths
     *
     * @return PathStack
     */
    public function get_paths(): SplStack
    {
        return $this->paths;
    }
    /**
     * Turn a template name into a possible filename based on configuration
     */
    private function normalize_template_name(string $name): string
    {
        // Ensure we have the expected file extension
        if (pathinfo($name, PATHINFO_EXTENSION) === '') {
            $name .= '.' . $this->default_suffix;
        }
        return $name;
    }
    /**
     * Retrieve the filesystem path to a view script
     *
     * @throws DomainException If the template requested includes directory traversal and LFI protection is on.
     */
    public function resolve(string $name): string|false
    {
        if ($this->lfi_protection_on && preg_match('#\.\.[\\\\/]#', $name)) {
            throw new DomainException('Requested scripts may not include parent directory traversal ("../", "..\" notation)');
        }
        if (!count($this->paths)) {
            return false;
        }
        $name = $this->normalize_template_name($name);
        return $this->resolve_to_path($name);
    }
    /** @return non-empty-string|false */
    private function resolve_to_path(string $name): string|false
    {
        foreach ($this->paths as $path) {
            $file = new Spl_File_Info($path . $name);
            if ($file->is_readable()) {
                // Found! Return it.
                $file_path = $file->get_real_path();
                if ($file_path === false && str_starts_with($path, 'phar://')) {
                    // Do not try to expand phar paths (realpath + phars == fail)
                    $file_path = $path . $name;
                    if (!file_exists($file_path)) {
                        break;
                    }
                }
                return $file_path;
            }
        }
        return false;
    }
}