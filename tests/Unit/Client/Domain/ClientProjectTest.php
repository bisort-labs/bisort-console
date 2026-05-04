<?php

declare(strict_types=1);

namespace App\Tests\Unit\Client\Domain;

use App\Client\Domain\ClientProject;
use PHPUnit\Framework\TestCase;

final class ClientProjectTest extends TestCase
{
    public function test_it_keeps_title_and_description_values(): void
    {
        $project = new ClientProject()
            ->setTitle('  Website Relaunch  ')
            ->setDescription('  Scope and delivery notes  ');

        self::assertSame('  Website Relaunch  ', $project->getTitle());
        self::assertSame('  Scope and delivery notes  ', $project->getDescription());
    }
}
