<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

$paths = [__DIR__.'/src'];

if (is_dir(__DIR__.'/tests')) {
    $paths[] = __DIR__.'/tests';
}

$rectorConfig = RectorConfig::configure()
    ->withPaths($paths)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        earlyReturn: true,
        symfonyCodeQuality: true,
    )
;

$containerXmlPath = __DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml';

if (is_file($containerXmlPath)) {
    $rectorConfig = $rectorConfig->withSymfonyContainerXml($containerXmlPath);
}

return $rectorConfig;
