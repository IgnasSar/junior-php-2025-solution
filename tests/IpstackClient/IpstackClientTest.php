<?php

declare(strict_types=1);

namespace App\Tests\IpstackClient;

use App\Client\IpstackClient;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class IpstackClientTest extends TestCase
{
    public function testGetIpData(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $httpClient->method('request')->willReturn($response);
        $response->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $response->method('toArray')->willReturn(['ip' => '1.1.1.1']);

        $client = new IpstackClient($httpClient);

        $data = $client->getIpData('1.1.1.1');

        $this->assertSame(['ip' => '1.1.1.1'], $data);
    }

    public function testGetIpDataThrowsOnFailure(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $httpClient->method('request')->willReturn($response);
        $response->method('getStatusCode')->willReturn(Response::HTTP_BAD_REQUEST);
        $response->method('getContent')->willReturn('error');

        $client = new IpstackClient($httpClient);

        $this->expectException(RuntimeException::class);

        $client->getIpData('1.1.1.1');
    }
}
