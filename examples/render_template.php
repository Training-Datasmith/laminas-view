<?php

declare(strict_types=1);

/**
 * Example: rendering PHP view templates with laminas-view.
 *
 * Run from the laminas-view project root:
 *   php examples/render_template.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Laminas\View\Model\ViewModel;

// --- Set up renderer with template path ---
$resolver = new TemplatePathStack([
    'script_paths' => [__DIR__ . '/templates'],
]);

$renderer = new PhpRenderer();
$renderer->setResolver($resolver);

// Create a tiny template for this example
$templateDir = __DIR__ . '/templates';
if (!is_dir($templateDir)) {
    mkdir($templateDir, 0755, true);
}

file_put_contents($templateDir . '/hello.phtml', <<<'PHP'
<h1><?= $this->escapeHtml($name) ?></h1>
<p>You have <?= (int)$count ?> messages.</p>
<?php if ($count > 0): ?>
<ul>
<?php foreach ($items as $item): ?>
  <li><?= $this->escapeHtml($item) ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
PHP);

// --- Render using a ViewModel ---
$model = new ViewModel([
    'name'  => 'Alice <script>alert(1)</script>',
    'count' => 3,
    'items' => ['Message A', 'Message B', 'Message C'],
]);
$model->setTemplate('hello');

$output = $renderer->render($model);
echo $output;

// Cleanup
@unlink($templateDir . '/hello.phtml');
@rmdir($templateDir);
