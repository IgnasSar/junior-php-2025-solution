<?php

declare(strict_types=1);

namespace App\Service\IpAddress;

use App\Client\IpstackClient;
use App\Dto\IpsRequest;
use App\Normalizer\IpAddressNormalizer;
use App\Repository\IpAddressRepository;
use App\Repository\IpBlacklistRepository;
use DateTimeImmutable;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class IpAddressQueryService
{
    private const DAY_DECREMENT = '-1 day';

    public function __construct(
        private readonly IpAddressRepository     $ipAddressRepository,
        private readonly IpAddressNormalizer     $ipAddressNormalizer,
        private readonly IpstackClient           $ipstackClient,
        private readonly IpAddressCommandService $ipAddressCommandService,
        private readonly IpBlacklistRepository   $ipBlacklistRepository,
    )
    {}

    public function getAll(IpsRequest $ipsRequest): array
    {
        $results = [];

        $ipBlacklistArray = $this->ipBlacklistRepository
            ->findAllByIp($ipsRequest->ips);

        foreach ($ipBlacklistArray as $ipBlacklist) {
            if (null !== $ipBlacklist) {
                throw new AccessDeniedHttpException(
                    "Ip {$ipBlacklist->getIpAddress()->getIp()} is blacklisted"
                );
            }
        }

        $ipAddressArray = $this->ipAddressRepository->findAllByIp($ipsRequest->ips);

        $ipAddressMap = [];
        foreach ($ipAddressArray as $ipAddressEntity) {
            $ipAddressMap[$ipAddressEntity->getIp()] = $ipAddressEntity;
        }

        $oneDayAgo = new DateTimeImmutable(self::DAY_DECREMENT);

        foreach ($ipsRequest->ips as $ip) {
            $ipAddress = $ipAddressMap[$ip] ?? null;

            if (null === $ipAddress) {
                $ipAddress = $this->ipAddressCommandService
                    ->create($ip, $this->ipstackClient->getIpData($ip));

            } elseif (
                $ipAddress->getUpdatedAt() < $oneDayAgo
            ) {
                $ipAddress = $this->ipAddressCommandService
                    ->update($ipAddress, $this->ipstackClient->getIpData($ip));
            }

            $results[] = $this->ipAddressNormalizer->normalize($ipAddress);
        }

        return $results;
    }
}
