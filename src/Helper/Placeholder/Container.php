<?php

declare (strict_types=1);
namespace Laminas\View\Helper\Placeholder;

use function array_unshift;
use function array_values;
use ArrayIterator;
use function assert;
use Closure;
use function count;
use Countable;
use IteratorAggregate;
use Laminas\View\Exception\RuntimeException;
use function ob_get_clean;
use function ob_start;
use Traversable;
/**
 * Container for placeholder values
 *
 * This class is not part of the public API and has no BC guarantees
 *
 * @internal
 *
 * @template TValue
 * @implements IteratorAggregate<int, TValue>
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final class Container implements Countable, IteratorAggregate
{
    /** @var array<int, TValue> */
    private array $items = [];
    private bool $capture_lock = false;
    /** @param TValue $item */
    public function append(mixed $item): void
    {
        $this->items[] = $item;
    }
    /** @param TValue $item */
    public function prepend(mixed $item): void
    {
        array_unshift($this->items, $item);
    }
    /** @param TValue $item */
    public function set(mixed $item): void
    {
        $this->items = [$item];
    }
    /** @param TValue $item */
    public function remove(mixed $item): void
    {
        foreach ($this->items as $key => $remove) {
            if ($item !== $remove) {
                continue;
            }
            unset($this->items[$key]);
            return;
        }
    }
    /**
     * @param Closure(TValue): bool $matcher
     * @return TValue|null
     */
    public function first_match(Closure $matcher): mixed
    {
        foreach ($this->items as $item) {
            if ($matcher($item) === true) {
                return $item;
            }
        }
        return null;
    }
    /** @return Traversable<int, TValue> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
    /** @return list<TValue> */
    public function to_array(): array
    {
        return array_values($this->items);
    }
    public function count(): int
    {
        return count($this->items);
    }
    public function is_capturing(): bool
    {
        return $this->capture_lock;
    }
    /**
     * @throws RuntimeException If a capture is already in progress.
     */
    public function capture_start(): void
    {
        if ($this->capture_lock) {
            throw new RuntimeException('This container is already capturing output');
        }
        $this->capture_lock = true;
        ob_start();
    }
    /**
     * @throws RuntimeException If a capture is not in progress.
     */
    public function capture_end(): string
    {
        if (!$this->capture_lock) {
            throw new RuntimeException('This container is not currently capturing output');
        }
        $content = ob_get_clean();
        assert($content !== false);
        $this->capture_lock = false;
        return $content;
    }
}