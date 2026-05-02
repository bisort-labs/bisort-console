<?php

declare(strict_types=1);

namespace App\User\Application;

use App\User\Domain\User;
use DomainException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class CreateUser
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function process(User $user): void
    {
        $this->normalizeUsername($user);
        $this->hashPassword($user);
    }

    private function normalizeUsername(User $user): void
    {
        $username = $user->getUsername();

        if ($username === null || trim($username) === '') {
            throw new DomainException('Username cannot be empty.');
        }

        $user->setUsername(mb_strtolower(trim($username)));
    }

    private function hashPassword(User $user): void
    {
        $plainPassword = $user->getPassword();

        if ($plainPassword === null || $plainPassword === '') {
            return;
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword),
        );
    }
}
