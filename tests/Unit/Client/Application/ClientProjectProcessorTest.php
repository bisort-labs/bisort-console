<?php

declare(strict_types=1);

namespace App\Tests\Unit\Client\Application;

use App\Client\Application\ClientProjectProcessor;
use App\Client\Domain\ClientProject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class ClientProjectProcessorTest extends TestCase
{
    public function test_it_generates_slug_from_title(): void
    {
        $project = new ClientProject()
            ->setTitle('Client Portal Relaunch')
            ->setDescription('Valid project description');
        $processor = new ClientProjectProcessor(new AsciiSlugger());

        $processor->process($project);

        self::assertSame('client-portal-relaunch', $project->getSlug());
    }

    public function test_it_preserves_existing_valid_data(): void
    {
        $project = new ClientProject()
            ->setTitle('Client Portal Relaunch')
            ->setDescription('Valid project description');
        $processor = new ClientProjectProcessor(new AsciiSlugger());

        $processor->process($project);

        self::assertSame('Client Portal Relaunch', $project->getTitle());
        self::assertSame('Valid project description', $project->getDescription());
    }
}
