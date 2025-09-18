<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class IpAddressControllerTest extends WebTestCase
{
    public function testGetIpAddresses(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/ip-addresses',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['ips' => ['1.1.1.1']])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testDeleteIpAddresses(): void
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            '/api/ip-addresses',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['ips' => ['1.1.1.1']])
        );

        $this->assertContains(
            $client->getResponse()->getStatusCode(),
            [Response::HTTP_NO_CONTENT, Response::HTTP_NOT_FOUND]
        );
    }
}
