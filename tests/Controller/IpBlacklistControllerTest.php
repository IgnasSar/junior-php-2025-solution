<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class IpBlacklistControllerTest extends WebTestCase
{
    public function testCreateBlacklist(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/ip-blacklist',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['ips' => ['1.1.1.1']])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testDeleteBlacklist(): void
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            '/api/ip-blacklist',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['ips' => ['1.1.1.1']])
        );

        $this->assertContains(
            $client->getResponse()->getStatusCode(),
            [Response::HTTP_NO_CONTENT, Response::HTTP_NOT_FOUND]
        );
    }
}
