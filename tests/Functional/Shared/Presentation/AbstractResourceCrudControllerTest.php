<?php

declare(strict_types=1);

namespace App\Tests\Functional\Shared\Presentation;

use App\Client\Domain\ClientProject;
use App\Client\Presentation\ClientProjectCrudController;
use App\Tests\Functional\FunctionalDatabaseTestCase;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\Request;

final class AbstractResourceCrudControllerTest extends FunctionalDatabaseTestCase
{
    /**
     * @throws Exception
     */
    public function test_index_query_hides_deleted_resources_by_default(): void
    {
        $client = self::createClient();
        $this->resetDatabase();
        $client->loginUser($this->createAdminUser());
        $this->createClientProject('Visible Project');
        $deletedProject = $this->createClientProject('Deleted Project');
        $deletedProject->markAsDeleted();
        $this->getEntityManager()->flush();

        $client->request(Request::METHOD_GET, '/admin/client-project');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Visible Project', $client->getResponse()->getContent());
        self::assertStringNotContainsString('Deleted Project', $client->getResponse()->getContent());
    }

    /**
     * @throws Exception
     */
    public function test_deleted_at_filter_disables_default_hidden_deleted_constraint(): void
    {
        $client = self::createClient();
        $this->resetDatabase();
        $client->loginUser($this->createAdminUser());
        $this->createClientProject('Visible Project');
        $deletedProject = $this->createClientProject('Deleted Project');
        $deletedProject->markAsDeleted();
        $this->getEntityManager()->flush();

        $client->request(Request::METHOD_GET, '/admin/client-project', [
            'filters' => [
                'deletedAt' => 'not_null',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Deleted Project', $client->getResponse()->getContent());
        self::assertStringNotContainsString('Visible Project', $client->getResponse()->getContent());
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception
     */
    public function test_it_soft_deletes_resource(): void
    {
        self::bootKernel();
        $this->resetDatabase();
        $this->createAdminUser();
        $project = $this->createClientProject();
        $projectId = $project->getId();

        self::getContainer()->get(ClientProjectCrudController::class)->deleteEntity($this->getEntityManager(), $project);
        $this->getEntityManager()->clear();

        $deletedProject = $this->getEntityManager()->find(ClientProject::class, $projectId);

        self::assertInstanceOf(ClientProject::class, $deletedProject);
        self::assertTrue($deletedProject->isDeleted());
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception
     */
    public function test_restore_action_clears_deleted_at(): void
    {
        $client = self::createClient();
        $this->resetDatabase();
        $client->loginUser($this->createAdminUser());
        $project = $this->createClientProject();
        $project->markAsDeleted();
        $this->getEntityManager()->flush();
        $projectId = $project->getId();

        $client->request(Request::METHOD_GET, sprintf('/admin/client-project/%d/restore', $projectId));
        $this->getEntityManager()->clear();

        $restoredProject = $this->getEntityManager()->find(ClientProject::class, $projectId);

        self::assertTrue($client->getResponse()->isRedirection());
        self::assertInstanceOf(ClientProject::class, $restoredProject);
        self::assertFalse($restoredProject->isDeleted());
    }
}
