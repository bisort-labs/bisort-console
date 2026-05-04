<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Client\Domain\ClientProject;
use App\Client\Domain\Deal;
use App\Client\Domain\Enum\DealStage;
use App\Client\Domain\Enum\LeadSource;
use App\Client\Domain\Lead;
use App\User\Domain\User;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalDatabaseTestCase extends WebTestCase
{
    /**
     * @throws Exception
     */
    protected function resetDatabase(): void
    {
        $connection = $this->getEntityManager()->getConnection();
        $connection->executeStatement('TRUNCATE TABLE deal, lead, client_projects, "user" RESTART IDENTITY CASCADE');
    }

    protected function createAdminUser(): User
    {
        $user = new User()
            ->setUsername('admin')
            ->setRoles(['ROLE_USER'])
            ->setPassword(password_hash('admin-password', PASSWORD_DEFAULT));

        $this->persistAndFlush($user);

        return $user;
    }

    protected function createClientProject(string $title = 'Client Portal'): ClientProject
    {
        $project = new ClientProject()
            ->setTitle($title)
            ->setSlug(strtolower(str_replace(' ', '-', $title)))
            ->setDescription('Project description');

        $this->persistAndFlush($project);

        return $project;
    }

    protected function createLead(string $name = 'Ada Lovelace'): Lead
    {
        $lead = new Lead()
            ->setName($name)
            ->setEmail('ada@example.com')
            ->setSource(LeadSource::Website);

        $this->persistAndFlush($lead);

        return $lead;
    }

    protected function createDeal(?Lead $lead = null): Deal
    {
        $deal = new Deal()
            ->setLead($lead ?? $this->createLead())
            ->setTitle('Website Deal')
            ->setCurrency('EUR')
            ->setExpectedValueCents(250000)
            ->setStage(DealStage::New);

        $this->persistAndFlush($deal);

        return $deal;
    }

    protected function persistAndFlush(object $entity): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
