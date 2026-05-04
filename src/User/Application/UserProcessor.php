<?php

declare(strict_types=1);

namespace App\User\Application;

use App\Shared\Application\ResourceProcessorInterface;
use App\Shared\Domain\AbstractResource;
use App\User\Domain\User;
use DomainException;
use Override;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ResourceProcessorInterface<User>
 */
final readonly class UserProcessor implements ResourceProcessorInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Override]
    public function supports(AbstractResource $resource): bool
    {
        return $resource instanceof User;
    }

    #[Override]
    public function process(object $entity): User
    {
        $this->normalizeUsername($entity);
        $this->hashPassword($entity);

        return $entity;
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

        if (password_get_info($plainPassword)['algo'] !== null) {
            return;
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword),
        );
    }
}
