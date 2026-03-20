<?php

declare (strict_types=1);
namespace Laminas\View\Helper;

use function implode;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\Placeholder\Container;
use Laminas\View\Helper\Placeholder\Position;
use Stringable;
/**
 * Helper for aggregating string content between otherwise segregated Views.
 */
final class Placeholder implements Stateful_Helper_Interface, Stringable
{
    /**
     * Placeholder Containers
     *
     * @var array<string, Container<string>>
     */
    private array $items = [];
    private string|null $current_container = null;
    private string $separator;
    /** @var array<string, Position> */
    private array $capture_position = [];
    public function __construct(private readonly string $default_separator = '')
    {
        $this->separator = $this->default_separator;
    }
    public function reset_state(): void
    {
        foreach ($this->items as $container) {
            if ($container->is_capturing()) {
                $container->capture_end();
            }
        }
        $this->separator = $this->default_separator;
        $this->items = [];
        $this->current_container = null;
        $this->capture_position = [];
    }
    /**
     * Instance Accessor
     */
    public function __invoke(string|null $placeholder = null): self
    {
        if ($placeholder !== null) {
            $this->container($placeholder);
        }
        return $this;
    }
    /** @return Container<string> */
    private function container(string $name): Container
    {
        $this->current_container = $name;
        if (!isset($this->items[$name])) {
            /** @psalm-var Container<string> */
            $this->items[$name] = new Container();
        }
        return $this->items[$name];
    }
    public function container_exists(string $name): bool
    {
        return isset($this->items[$name]);
    }
    private function name(string|null $name): string
    {
        $name ??= $this->current_container;
        if ($name === null) {
            throw new RuntimeException('Cannot determine the name of the placeholder');
        }
        return $name;
    }
    public function append(string $content, string|null $placeholder = null): self
    {
        $this->container($this->name($placeholder))->append($content);
        return $this;
    }
    public function prepend(string $content, string|null $placeholder = null): self
    {
        $this->container($this->name($placeholder))->prepend($content);
        return $this;
    }
    public function set(string $content, string|null $placeholder = null): self
    {
        $this->container($this->name($placeholder))->set($content);
        return $this;
    }
    public function to_string(string|null $placeholder = null): string
    {
        return implode($this->separator, $this->container($this->name($placeholder))->to_array());
    }
    public function __toString(): string
    {
        return $this->to_string();
    }
    public function capture_start(string|null $placeholder = null, Position $position = Position::Append): void
    {
        $name = $this->name($placeholder);
        $this->container($name)->capture_start();
        $this->capture_position[$name] = $position;
    }
    public function capture_end(string|null $placeholder = null): void
    {
        $name = $this->name($placeholder);
        $container = $this->container($name);
        $content = $container->capture_end();
        $position = $this->capture_position[$name];
        match ($position) {
            Position::Append => $container->append($content),
            Position::Prepend => $container->prepend($content),
            Position::Set => $container->set($content),
        };
    }
    public function set_separator(string $separator): self
    {
        $this->separator = $separator;
        return $this;
    }
}