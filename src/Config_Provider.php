<?php

declare (strict_types=1);
namespace Laminas\View;

use function class_exists;
use Laminas\Escaper\Escaper;
use Laminas\Escaper\Escaper_Interface;
use Laminas\Service_Manager\Factory\Invokable_Factory;
use Laminas\Service_Manager\Service_Manager;
use Laminas\View\Helper\Doctype;
use Symfony\Component\Console\Command\Command;
/**
 * @psalm-import-type DoctypeID from Doctype
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-type ViewConfigShape = array{
 *     dependencies: ServiceManagerConfiguration,
 *     view_helpers: ServiceManagerConfiguration,
 *     view_helper_config?: array{
 *         asset?: array{resource_map: array<non-empty-string, non-empty-string>},
 *         base_path?: non-empty-string|null,
 *         doctype?: DoctypeID,
 *         encoding?: string,
 *     },
 *     view_manager?: array{
 *         base_path?: non-empty-string|null,
 *         strict_variables?: bool,
 *         default_layout?: non-empty-string,
 *         default_capture_to?: non-empty-string,
 *         doctype?: DoctypeID,
 *         encoding?: non-empty-string,
 *         template_map?: array<string, string>,
 *         template_path_stack?: list<non-empty-string>,
 *         prefix_template_path_stack?: array<non-empty-string, non-empty-string>,
 *         default_template_suffix?: non-empty-string,
 *     },
 *     templates?: array{
 *         strict_variables?: bool,
 *         default_capture_to?: non-empty-string,
 *         extension?: non-empty-string,
 *         default_layout?: non-empty-string,
 *         map?: array<string, string>,
 *     },
 *     laminas-cli: array{
 *         commands: array<string, string>,
 *     }
 * }
 */
final readonly class Config_Provider
{
    /** @return ViewConfigShape */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->get_dependencies(),
            /**
             * The top-level configuration key for defining custom view helpers.
             *
             * This option should use the `ServiceManagerConfiguration` array format
             */
            'view_helpers' => [],
            'view_helper_config' => [
                /**
                 * The expected doctype of your HTML output.
                 *
                 * The doctype is used by a number of helpers to determine the correct output style of generated markup
                 */
                'doctype' => Doctype::HTML5,
                /**
                 * Encoding is passed to the Escaper which is consumed by a number of helpers
                 */
                'encoding' => 'utf-8',
                /**
                 * Maps asset names to resources for the `Asset` helper
                 */
                'asset' => ['resource_map' => []],
                /**
                 * The base path is a string provided to the BasePath view helper
                 */
                'base_path' => null,
            ],
            'view_manager' => [
                /**
                 * Strict variables controls whether an exception is thrown when a view template attempts to use a
                 * variable that has not been defined.
                 */
                'strict_variables' => true,
                /**
                 * Templates configured here will be provided to the TemplateMapResolver.
                 * This is conventional for an MVC app
                 */
                'template_map' => [],
                /**
                 * Templates configured here will be provided to the TemplatePathStack resolver.
                 * This is conventional for an MVC app
                 */
                'template_path_stack' => [],
                /**
                 * Templates configured here will be provided to the PrefixPathStackResolver
                 */
                'prefix_template_path_stack' => [],
            ],
            'templates' => [
                /**
                 * Strict variables controls whether an exception is thrown when a view template attempts to use a
                 * variable that has not been defined.
                 */
                'strict_variables' => true,
                /**
                 * Templates configured here will be provided to the TemplateMapResolver
                 * This is conventional for a Mezzio app
                 */
                'map' => [],
            ],
            'laminas-cli' => ['commands' => $this->cli_commands()],
        ];
    }
    /** @return array<string, class-string> */
    private function cli_commands(): array
    {
        if (class_exists(Command::class)) {
            return [Console\Generate_Template_Map_Command::DEFAULT_NAME => Console\Generate_Template_Map_Command::class];
        }
        return [];
    }
    /** @return ServiceManagerConfiguration */
    public function get_dependencies(): array
    {
        return ['factories' => [Console\Generate_Template_Map_Command::class => Invokable_Factory::class, Renderer\Php_Renderer::class => Renderer\Php_Renderer_Factory::class, Resolver\Aggregate_Resolver::class => Resolver\Factory\Aggregate_Resolver_Factory::class, Resolver\Prefix_Path_Stack_Resolver::class => Resolver\Factory\Prefix_Path_Stack_Resolver_Factory::class, Resolver\Template_Map_Resolver::class => Resolver\Factory\Template_Map_Resolver_Factory::class, Resolver\Template_Path_Stack::class => Resolver\Factory\Template_Path_Stack_Resolver_Factory::class, Escaper::class => Factory\Escaper_Factory::class, Helper_Plugin_Manager::class => Factory\Helper_Plugin_Manager_Factory::class, View::class => Factory\View_Factory::class], 'aliases' => [Escaper_Interface::class => Escaper::class, Helper_Plugin_Manager_Interface::class => Helper_Plugin_Manager::class, Renderer\Renderer_Interface::class => Renderer\Php_Renderer::class, Resolver\Resolver_Interface::class => Resolver\Aggregate_Resolver::class, View_Interface::class => View::class]];
    }
}