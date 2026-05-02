<?php

declare(strict_types=1);

$paths = [__DIR__.'/src'];

if (is_dir(__DIR__.'/tests')) {
    $paths[] = __DIR__.'/tests';
}

$finder = (new PhpCsFixer\Finder())
    ->in($paths)
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'strict_param' => true,
    ])
    ->setFinder($finder)
;
