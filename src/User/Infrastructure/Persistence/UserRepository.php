<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Persistence;

use App\User\Domain\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Override;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements UserProviderInterface<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface, UserProviderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    #[Override]
    public function loadUserByIdentifier(string $identifier): User
    {
        $user = $this->findOneBy([
            'username' => $identifier,
            'isActive' => true,
            'deletedAt' => null,
        ]);

        if (!$user instanceof User) {
            $exception = new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return $user;
    }

    #[Override]
    public function refreshUser(UserInterface $user): User
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    #[Override]
    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }

    #[Override]
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
