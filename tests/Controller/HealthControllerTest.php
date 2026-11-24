<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HealthControllerTest extends WebTestCase
{
    public function testAnonymousHealthCheckSucceeds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/healthz');

        self::assertResponseStatusCodeSame(204);
    }
}
