<?php

declare(strict_types=1);

use NunoMaduro\PhpInsights\Domain\Insights\CyclomaticComplexityIsHigh;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenDefineFunctions;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenFinalClasses;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenTraits;
use NunoMaduro\PhpInsights\Domain\Metrics\Architecture\Classes;
use SlevomatCodingStandard\Sniffs\Commenting\UselessFunctionDocCommentSniff;
use SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\AlphabeticallySortedUsesSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff;

return [
    'preset' => 'laravel',

    'ide' => 'phpstorm',

    'exclude' => [
        'bootstrap/cache',
        'public',
        'storage',
        'vendor',
    ],

    'add' => [
        Classes::class => [
            ForbiddenFinalClasses::class,
            CyclomaticComplexityIsHigh::class,
        ],
    ],

    'remove' => [
        AlphabeticallySortedUsesSniff::class,
        ForbiddenDefineFunctions::class,
        ForbiddenNormalClasses::class,
        ForbiddenTraits::class,
        UselessFunctionDocCommentSniff::class,
        ReturnTypeHintSniff::class,
    ],

    'config' => [
        'PHP_CodeSniffer\\Standards\\Generic\\Sniffs\\Files\\LineLengthSniff' => [
            'lineLimit' => 120,
            'absoluteLineLimit' => 160,
            'ignoreComments' => false,
        ],

        FunctionLengthSniff::class => [
            'maxLinesLength' => 25,
            'includeComments' => true,
        ],

        CyclomaticComplexityIsHigh::class => [
            'maxComplexity' => 10,
        ],
    ],

    'requirements' => [
        'min-quality' => 100,
        'min-complexity' => 100,
        'min-architecture' => 100,
        'min-style' => 100,
    ],

    'threads' => null,
    'timeout' => 60,
];
