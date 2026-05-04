<?php

declare(strict_types=1);

namespace App\Tests\Functional\User\Application;

use App\User\Application\UserProcessor;
use App\User\Domain\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserProcessorTest extends KernelTestCase
{
    public function test_it_hashes_plain_password(): void
    {
        $user = new User()
            ->setUsername('Admin')
            ->setPassword('plain-password');

        $this->getProcessor()->process($user);

        self::assertSame('admin', $user->getUsername());
        self::assertNotSame('plain-password', $user->getPassword());
        self::assertIsString($user->getPassword());
        self::assertTrue(password_verify('plain-password', $user->getPassword()));
    }

    public function test_it_does_not_overwrite_password_when_no_plain_password_is_provided(): void
    {
        $user = new User()->setUsername('Admin');

        $this->getProcessor()->process($user);

        self::assertSame('admin', $user->getUsername());
        self::assertNull($user->getPassword());
    }

    private function getProcessor(): UserProcessor
    {
        return self::getContainer()->get(UserProcessor::class);
    }
}
