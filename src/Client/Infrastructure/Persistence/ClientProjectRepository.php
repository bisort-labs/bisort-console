<?php

declare(strict_types=1);

namespace App\Client\Infrastructure\Persistence;

use App\Client\Domain\ClientProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClientProject>
 */
class ClientProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientProject::class);
    }
}
