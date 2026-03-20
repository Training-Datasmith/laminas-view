<?php

declare (strict_types=1);
namespace Laminas\View\Exception;

use Laminas\Service_Manager\Exception\Service_Not_Found_Exception;
use function sprintf;
use Throwable;
final class Rendering_Failed_Exception extends RuntimeException
{
    /** @param non-empty-string $filePath */
    public static function because_the_template_file_could_not_be_included(string $file_path): self
    {
        return new self(sprintf('Failed to render template because the template file could not be included: "%s"', $file_path));
    }
    public static function because_a_template_was_not_specified(): self
    {
        return new self('A template must be specified during rendering, ' . 'either as an argument or as a property of the view model');
    }
    /**
     * @param non-empty-string $name
     * @param non-empty-string $filePath
     */
    public static function because_of_access_to_an_undeclared_variable(string $name, string $file_path): self
    {
        return new self(sprintf('Access to an undeclared variable "%s" in the template "%s"', $name, $file_path));
    }
    /**
     * @param non-empty-string $name
     * @param non-empty-string $filePath
     */
    public static function because_member_variables_cannot_be_mutated(string $name, string $file_path): self
    {
        return new self(sprintf('Attempt to mutate the variable "%s" in the template "%s"', $name, $file_path));
    }
    public static function because_of_an_exception(string $template, Throwable $error): self
    {
        return new self(sprintf('An exception occurred during render of "%s" with the message: %s"', $template, $error->get_message()), 0, $error);
    }
    /** @param non-empty-string $pluginName */
    public static function because_of_a_plugin_exception(string $plugin_name, Throwable $error): self
    {
        return new self(sprintf('An exception occurred during execution of the plugin "%s". Message: %s', $plugin_name, $error->get_message()), 0, $error);
    }
    /** @param non-empty-string $template */
    public static function because_a_render_loop_has_been_detected(string $template): self
    {
        return new self(sprintf('A cyclic rendering dependency has been detected during render of the template "%s"', $template));
    }
    public static function because_the_template_cannot_be_resolved_to_a_file(string $template_name): self
    {
        return new self(sprintf('Unable to render template "%s"; resolver could not resolve to a file', $template_name));
    }
    public static function because_of_an_unknown_plugin(string $alias, string $template_path, Service_Not_Found_Exception $previous): self
    {
        return new self(sprintf('Access to an unknown view helper alias "%s" from the template "%s"', $alias, $template_path), 0, $previous);
    }
    public static function because_of_ambiguous_arguments_to_php_renderer(): self
    {
        return new self('Passing both view model and view variables to render is ambiguous. ' . 'Either provide just the model, or, a template name and the variables with ' . 'which to create the model.');
    }
}