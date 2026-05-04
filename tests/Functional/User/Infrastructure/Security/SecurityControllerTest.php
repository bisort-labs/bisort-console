<?php

declare(strict_types=1);

namespace App\Tests\Functional\User\Infrastructure\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class SecurityControllerTest extends WebTestCase
{
    public function test_login_route_renders_successfully(): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, '/login');

        self::assertResponseIsSuccessful();
    }

    public function test_logout_route_is_intercepted_by_security(): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, '/logout');

        self::assertTrue($client->getResponse()->isRedirection());
    }
}
