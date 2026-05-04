<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Tests\Functional\FunctionalDatabaseTestCase;
use Symfony\Component\HttpFoundation\Request;

final class AdminCrudSmokeTest extends FunctionalDatabaseTestCase
{
    /**
     * @dataProvider crudIndexPathProvider
     */
    public function test_crud_index_loads(string $path): void
    {
        $client = self::createClient();
        $this->resetDatabase();
        $client->loginUser($this->createAdminUser());

        $client->request(Request::METHOD_GET, $path);

        self::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function crudIndexPathProvider(): iterable
    {
        yield 'client projects' => ['/admin/client-project'];
        yield 'leads' => ['/admin/lead'];
        yield 'deals' => ['/admin/deal'];
        yield 'users' => ['/admin/user'];
    }
}
