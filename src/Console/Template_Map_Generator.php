<?php

declare (strict_types=1);
namespace Laminas\View\Console;

use function array_pop;
use function array_slice;
use function assert;
use function basename;
use const DIRECTORY_SEPARATOR;
use function dirname;
use function explode;
use function implode;
use function is_dir;
use function is_readable;
use function is_string;
use function is_writable;
use Laminas\View\Exception\InvalidArgumentException;
use function ltrim;
use const PHP_EOL;
use function realpath;
use Recursive_Directory_Iterator;
use Recursive_Iterator_Iterator;
use function sort;
use Spl_File_Info;
use function sprintf;
use function str_ends_with;
use function str_repeat;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strtolower;
use function substr;
/**
 * @internal
 *
 * @psalm-internal Laminas\View
 * @psalm-internal LaminasTest\View
 */
final readonly class Template_Map_Generator
{
    public string $directory_to_scan;
    public string $destination_file_path;
    public function __construct(string $directory_to_scan, string $destination_file_path, public string $file_suffix = 'phtml')
    {
        $directory = realpath($directory_to_scan);
        if (!is_string($directory) || !is_dir($directory) || !is_readable($directory)) {
            throw new InvalidArgumentException(sprintf('The given template directory does not exist, or it is not readable: %s', $directory_to_scan));
        }
        $this->directory_to_scan = $directory;
        if (!str_ends_with($destination_file_path, '.php')) {
            throw new InvalidArgumentException('A .php file name extension is required for the target file');
        }
        $destination_directory = realpath(dirname($destination_file_path));
        if (!is_string($destination_directory) || !is_writable($destination_directory)) {
            throw new InvalidArgumentException(sprintf('The target directory is not writable: %s', dirname($destination_file_path)));
        }
        $this->destination_file_path = $destination_directory . DIRECTORY_SEPARATOR . basename($destination_file_path);
    }
    public function __invoke(): string
    {
        $relative_path = $this->compute_relative_path();
        $map = [];
        foreach ($this->find_templates() as $template) {
            $map[] = sprintf("%s'%s' => __DIR__ . '%s%s%s',", str_repeat(' ', 12), $this->name($template), DIRECTORY_SEPARATOR, str_repeat('../', $relative_path['ascend']), ltrim(str_replace($relative_path['ancestor'], '', $template), DIRECTORY_SEPARATOR));
        }
        $entries = implode(PHP_EOL, $map);
        return <<<PHP
        <?php
        declare(strict_types=1);
        
        return [
            'templates' => [
                'map' => [
        {$entries}
                ],
            ],
        ];
        
        PHP;
    }
    /**
     * @return array{
     *     ancestor: string,
     *     ascend: int,
     * }
     */
    private function compute_relative_path(): array
    {
        $dir = dirname($this->destination_file_path);
        $up = 0;
        while (str_starts_with($this->directory_to_scan, $dir) === false) {
            $dir = implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $dir), 0, -1));
            $up++;
        }
        assert($dir !== '');
        return ['ancestor' => $dir, 'ascend' => $up];
    }
    /**
     * Create a 'name' for the template based on its file path
     *
     * @param non-empty-string $file
     */
    private function name(string $file): string
    {
        $node = explode(DIRECTORY_SEPARATOR, str_replace($this->directory_to_scan, '', $file));
        $last_part = array_pop($node);
        $last_part = substr($last_part, 0, -(strlen($this->file_suffix) + 1));
        return ltrim(implode('/', $node) . '/' . $last_part, '/');
    }
    /** @return list<non-empty-string> */
    private function find_templates(): array
    {
        $rdi = new Recursive_Directory_Iterator($this->directory_to_scan, Recursive_Directory_Iterator::FOLLOW_SYMLINKS | Recursive_Directory_Iterator::SKIP_DOTS);
        $rii = new Recursive_Iterator_Iterator($rdi, Recursive_Iterator_Iterator::LEAVES_ONLY);
        $files = [];
        foreach ($rii as $file) {
            assert($file instanceof Spl_File_Info);
            if (strtolower($file->get_extension()) !== $this->file_suffix) {
                continue;
            }
            $real_path = $file->get_real_path();
            assert($real_path !== false);
            $files[] = $real_path;
        }
        sort($files);
        return $files;
    }
}