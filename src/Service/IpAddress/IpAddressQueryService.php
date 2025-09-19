<?php

declare(strict_types=1);

namespace App\Service\IpAddress;

use App\Client\IpstackClient;
use App\Dto\IpsRequest;
use App\Entity\IpAddress;
use App\Entity\IpBlacklist;
use App\Normalizer\IpAddressNormalizer;
use App\Service\Helper\IpPrefetchService;
use DateTimeImmutable;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IpAddressQueryService
{
    private const DAY_DECREMENT = '-1 day';

    public function __construct(
        private readonly IpAddressNormalizer     $ipAddressNormalizer,
        private readonly IpstackClient           $ipstackClient,
        private readonly IpAddressCommandService $ipAddressCommandService,
        private readonly IpPrefetchService $ipPrefetchService
    )
    {}

    /**
     * Get information about a single IP address.
     * Validates IP format and checks blacklist before fetching or updating.
     */
    public function getOne(string $ip): array
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new BadRequestHttpException("Invalid IP address: {$ip}");
        }

        [$ipBlacklistMap, $ipAddressMap] =
            $this->ipPrefetchService->mapPrefetchedData([$ip]);

        $ipAddress = $this->processIp($ip, $ipBlacklistMap, $ipAddressMap);

        return $this->ipAddressNormalizer->normalize($ipAddress);
    }

    /**
     * Get information about multiple IPs in bulk.
     * Uses prefetching for efficiency and normalizes each IP address.
     */
    public function getAll(IpsRequest $ipsRequest): array
    {
        [$ipBlacklistMap, $ipAddressMap] =
            $this->ipPrefetchService->mapPrefetchedData($ipsRequest->ips);

        $ipAddresses = [];
        foreach ($ipsRequest->ips as $ip) {
            $ipAddress = $this->processIp($ip, $ipBlacklistMap, $ipAddressMap);
            $ipAddresses[] = $this->ipAddressNormalizer->normalize($ipAddress);
        }

        return $ipAddresses;
    }

    /**
     * Process an IP: check blacklist, create new entry if missing, update if outdated.
     *
     * @param IpBlacklist[] $ipBlacklistMap
     * @param IpAddress[] $ipAddressMap
     * @return IpAddress
     */
    private function processIp(string $ip, array $ipBlacklistMap, array $ipAddressMap): object
    {
        $oneDayAgo = new DateTimeImmutable(self::DAY_DECREMENT);

        if (isset($ipBlacklistMap[$ip])) {
            throw new AccessDeniedHttpException("Ip {$ip} is blacklisted");
        }

        $ipAddress = $ipAddressMap[$ip] ?? null;

        if (null === $ipAddress) {
            return $this->ipAddressCommandService
                ->create($ip, $this->ipstackClient->getIpData($ip));
        }

        if ($ipAddress->getUpdatedAt() < $oneDayAgo) {
            return $this->ipAddressCommandService
                ->update($ipAddress, $this->ipstackClient->getIpData($ip));
        }

        return $ipAddress;
    }
}
