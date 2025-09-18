<?php

declare(strict_types=1);

namespace App\Client;

use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;

class IpstackClient{
    public function __construct(
        private readonly HttpClientInterface $ipstackClient
    ) {}

    public function getIpData(string $ip): array
    {
        $response = $this->ipstackClient->request('GET', $ip);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new RuntimeException("Ipstack API failed for ip: {$ip}");
        }

        return $response->toArray(false);
    }
}