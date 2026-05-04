<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain;

use App\User\Domain\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function test_it_exposes_identifier_roles_and_password(): void
    {
        $user = new User()
            ->setUsername('admin')
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER', 'ROLE_ADMIN'])
            ->setPassword('hashed-password');

        self::assertSame('admin', $user->getUserIdentifier());
        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());
        self::assertSame('hashed-password', $user->getPassword());
    }
}
