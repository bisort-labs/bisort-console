<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Domain;

use App\Shared\Domain\AbstractResource;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AbstractResourceTest extends TestCase
{
    public function test_it_sets_and_updates_timestamps(): void
    {
        $resource = new TestResource();

        $resource->markCreated();
        $createdAt = $resource->getCreatedAt();
        $initialUpdatedAt = $resource->getUpdatedAt();

        usleep(1000);
        $resource->markUpdated();

        self::assertInstanceOf(DateTimeImmutable::class, $createdAt);
        self::assertSame($createdAt, $initialUpdatedAt);
        self::assertInstanceOf(DateTimeImmutable::class, $resource->getUpdatedAt());
        self::assertNotSame($initialUpdatedAt, $resource->getUpdatedAt());
    }

    public function test_it_soft_deletes_resource(): void
    {
        $resource = new TestResource();

        $resource->markAsDeleted();

        self::assertInstanceOf(DateTimeImmutable::class, $resource->getDeletedAt());
        self::assertFalse($resource->isActive());
    }

    public function test_it_restores_resource(): void
    {
        $resource = new TestResource();

        $resource->markAsDeleted();
        $resource->restore();

        self::assertNull($resource->getDeletedAt());
        self::assertTrue($resource->isActive());
    }

    public function test_it_reports_deleted_state(): void
    {
        $resource = new TestResource();

        self::assertFalse($resource->isDeleted());

        $resource->markAsDeleted();

        self::assertTrue($resource->isDeleted());
    }
}

final class TestResource extends AbstractResource
{
}
