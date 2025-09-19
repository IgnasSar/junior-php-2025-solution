<?php

declare(strict_types=1);

namespace App\Service\Helper;

use App\Repository\IpAddressRepository;
use App\Repository\IpBlacklistRepository;

class IpPrefetchService {
    public function __construct(
        private readonly IpAddressRepository $ipAddressRepository,
        private readonly IpBlacklistRepository $ipBlacklistRepository
    ) {}

    /**
     * Prefetches IpAddress and IpBlacklist entities for a list of IPs,
     * providing fast lookup maps for IP data and blacklist status.
     *
     * @param string[] $ips
     * @return array[]
     */
    public function mapPrefetchedData(array $ips): array
    {
        $ipBlacklistMap = [];
        foreach ($this->ipBlacklistRepository->findAllByIp($ips) as $ipBlacklist) {
            $ipBlacklistMap[$ipBlacklist->getIpAddress()->getIp()] = true;
        }

        $ipAddressMap = [];
        foreach ($this->ipAddressRepository->findAllByIp($ips) as $ipAddress) {
            $ipAddressMap[$ipAddress->getIp()] = $ipAddress;
        }

        return [$ipBlacklistMap, $ipAddressMap];
    }
}
