<?php

declare (strict_types=1);
namespace Laminas\View\Renderer;

use function array_filter;
use function array_key_exists;
use ArrayIterator;
use function assert;
use function extract;
use function is_callable;
use function is_string;
use function str_starts_with;
use IteratorAggregate;
use Laminas\Service_Manager\Exception\Service_Not_Found_Exception;
use Laminas\View\Exception\Rendering_Failed_Exception;
use Laminas\View\Helper\Helper_Interface;
use Laminas\View\Helper_Plugin_Manager_Interface;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use Throwable;
use Traversable;
/**
 * phpcs:disable WebimpressCodingStandard.NamingConventions.ValidVariableName
 * phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 *
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 * @psalm-no-seal-properties Magic properties are retrieved from this class to fetch view variables
 * @implements IteratorAggregate<string, mixed>
 * @psalm-api
 */
final class Template implements IteratorAggregate
{
    private bool $__render_lock = false;
    /**
     * @param non-empty-string $__template
     * @param array<string, mixed> $__variables
     */
    public function __construct(private readonly string $__template, private readonly array $__variables, private readonly Helper_Plugin_Manager_Interface $__plugins, private readonly bool $__strict_variables = true)
    {
    }
    /** @throws RenderingFailedException If there are any errors or violations during rendering. */
    public function __invoke(): string
    {
        // This variable locks rendering of _this_ instance and prevents calls to $this->__invoke() from causing
        // infinite loops.
        if ($this->__render_lock) {
            throw Rendering_Failed_Exception::because_a_render_loop_has_been_detected($this->__template);
        }
        try {
            $this->__render_lock = true;
            ob_start();
            // Extract variables into local scope, excluding internal double-underscore variables
            $__local_vars = array_filter($this->__variables, static fn(string $k): bool => !str_starts_with($k, '__'), ARRAY_FILTER_USE_KEY);
            extract($__local_vars);
            // phpcs:ignore Generic.PHP.ForbiddenFunctions
            unset($__local_vars);
            /**
             * @psalm-var mixed $include
             * @psalm-suppress UnresolvableInclude
             */
            $include = include $this->__template;
            if ($include === false) {
                throw Rendering_Failed_Exception::because_the_template_file_could_not_be_included($this->__template);
            }
            $content = ob_get_clean();
            assert(is_string($content));
            $this->__render_lock = false;
            return $content;
        } catch (Throwable $error) {
            ob_end_clean();
            if ($error instanceof Rendering_Failed_Exception) {
                throw $error;
                // Do not wrap our own exceptions
            }
            throw Rendering_Failed_Exception::because_of_an_exception($this->__template, $error);
        }
    }
    /**
     * Allows variable retrieval only from the composed array of variables
     *
     * @param non-empty-string $name
     */
    public function __get(string $name): mixed
    {
        if (!array_key_exists($name, $this->__variables) && $this->__strict_variables) {
            throw Rendering_Failed_Exception::because_of_access_to_an_undeclared_variable($name, $this->__template);
        }
        return $this->__variables[$name] ?? null;
    }
    /**
     * Prevent overloading of member variables
     *
     * @param non-empty-string $name
     */
    public function __set(string $name, mixed $_value): never
    {
        throw Rendering_Failed_Exception::because_member_variables_cannot_be_mutated($name, $this->__template);
    }
    public function __isset(string $name): bool
    {
        return isset($this->__variables[$name]);
    }
    /**
     * Proxies calls to unknown methods to the helper plugin manager
     *
     * @param non-empty-string $method
     * @param array<non-empty-string, mixed> $args
     */
    public function __call(string $method, array $args): mixed
    {
        try {
            $plugin = $this->__plugins->get($method);
        } catch (Service_Not_Found_Exception $e) {
            throw Rendering_Failed_Exception::because_of_an_unknown_plugin($method, $this->__template, $e);
        }
        assert(is_callable($plugin) || $plugin instanceof Helper_Interface);
        try {
            /** @psalm-var mixed $returnValue */
            $return_value = is_callable($plugin) ? $plugin(...$args) : $plugin;
            return $return_value;
        } catch (Throwable $e) {
            throw Rendering_Failed_Exception::because_of_a_plugin_exception($method, $e);
        }
    }
    /** @return Traversable<string, mixed> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->__variables);
    }
}