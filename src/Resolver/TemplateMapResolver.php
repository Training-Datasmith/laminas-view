<?php

declare (strict_types=1);
namespace Laminas\View\Resolver;

use function array_key_exists;
use function array_replace_recursive;
use ArrayIterator;
use function is_array;
use function is_iterable;
use function is_string;
use function iterator_to_array;
use IteratorAggregate;
use Laminas\View\Exception\InvalidArgumentException;
use function sprintf;
use Traversable;
/** @implements IteratorAggregate<non-empty-string, non-empty-string> */
final class Template_Map_Resolver implements IteratorAggregate, Resolver_Interface
{
    /** @var array<non-empty-string, non-empty-string> */
    private array $map = [];
    /** @param iterable<non-empty-string, non-empty-string> $map */
    public function __construct(iterable $map = [])
    {
        $this->set_map($map);
    }
    /** @return Traversable<non-empty-string, non-empty-string> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->map);
    }
    /**
     * Set (overwrite) template map
     *
     * Maps should be arrays with name => path pairs
     *
     * @param iterable<string, string> $map
     * @throws InvalidArgumentException
     */
    public function set_map(iterable $map): void
    {
        foreach ($map as $name => $value) {
            $this->assert_map($name, $value);
        }
        /** @psalm-var iterable<non-empty-string, non-empty-string> $map */
        $this->map = is_array($map) ? $map : iterator_to_array($map);
    }
    /**
     * @psalm-assert non-empty-string $name
     * @psalm-assert non-empty-string $value
     * @throws InvalidArgumentException
     */
    private function assert_map(int|string $name, mixed $value): void
    {
        if (!is_string($name) || !is_string($value) || $name === '' || $value === '') {
            throw new InvalidArgumentException(sprintf('Template names and values should be non-empty strings. Received `%s => %s`', $name, (string) $value));
        }
    }
    /**
     * Add an entry to the map
     *
     * A hash map can be passed as the first argument to perform a merge
     *
     * @param string|iterable<string, string> $nameOrMap
     * @throws InvalidArgumentException
     */
    public function add(string|iterable $name_or_map, string|null $path = null): void
    {
        if (is_string($name_or_map) && is_string($path)) {
            $this->assert_map($name_or_map, $path);
            $this->merge([$name_or_map => $path]);
            return;
        }
        if (is_iterable($name_or_map)) {
            $this->merge($name_or_map);
            return;
        }
        throw new InvalidArgumentException('Either specify both $nameOrMap and $path as strings, or, $nameOrMap as an iterable');
    }
    /**
     * Merge internal map with provided map
     *
     * @param iterable<string, string> $map
     * @throws InvalidArgumentException
     */
    public function merge(iterable $map): void
    {
        foreach ($map as $name => $value) {
            $this->assert_map($name, $value);
        }
        $map = is_array($map) ? $map : iterator_to_array($map);
        /** @psalm-var array<non-empty-string, non-empty-string> $result */
        $result = array_replace_recursive($this->map, $map);
        $this->map = $result;
    }
    /**
     * Does the resolver contain an entry for the given name?
     *
     * @param non-empty-string $name
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->map);
    }
    /**
     * Retrieve a template path by name
     *
     * @param non-empty-string $name
     * @return non-empty-string
     * @throws TemplateCannotBeFound If no entry exists.
     */
    public function get(string $name): string
    {
        if (!$this->has($name)) {
            throw Template_Cannot_Be_Found::by_name($name);
        }
        return $this->map[$name];
    }
    /**
     * Retrieve the template map
     *
     * @return array<non-empty-string, non-empty-string>
     */
    public function get_map(): array
    {
        return $this->map;
    }
    public function resolve(string $name): string|false
    {
        return $this->map[$name] ?? false;
    }
}