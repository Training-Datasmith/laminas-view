<?php

declare (strict_types=1);
namespace Laminas\View\Console;

use function assert;
use function file_put_contents;
use function is_string;
use Laminas\View\Exception\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Console\Style\Symfony_Style;
use function trim;
final class Generate_Template_Map_Command extends Command
{
    public const DEFAULT_NAME = 'generate-template-map';
    public function __construct()
    {
        parent::__construct(self::DEFAULT_NAME);
    }
    protected function configure(): void
    {
        $this->set_description(<<<'TXT'
        Scans a directory of templates and creates a configuration file to use as a template map for laminas-view
        TXT);
        $this->add_argument('templates', Input_Argument::REQUIRED, 'The path to the template directory to scan for files');
        $this->add_argument('config', Input_Argument::REQUIRED, <<<'TXT'
        The path to the configuration file you wish to create.
        The parent directory must exist and the file name must end with ".php".
        Example: './config/autoload/my-module-templates.global.php'
        TXT);
        $this->add_argument('suffix', Input_Argument::OPTIONAL, <<<'TXT'
        The filename suffix of your template files.
        Defaults to `phtml`
        TXT, 'phtml');
    }
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $style = new Symfony_Style($input, $output);
        $scan = $input->get_argument('templates');
        $target = $input->get_argument('config');
        $suffix = $input->get_argument('suffix');
        assert(is_string($scan) && is_string($target) && is_string($suffix));
        try {
            $generator = new Template_Map_Generator($scan, $target, trim($suffix, '.'));
        } catch (InvalidArgumentException $e) {
            $style->error($e->get_message());
            return self::INVALID;
        }
        $write = file_put_contents($target, $generator());
        if ($write === false) {
            $style->error('Failed to write map to ' . $target);
            return self::FAILURE;
        }
        $style->success('Wrote template map to ' . $target);
        return self::SUCCESS;
    }
}