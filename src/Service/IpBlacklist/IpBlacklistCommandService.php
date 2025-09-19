<?php

declare(strict_types=1);

namespace App\Service\IpBlacklist;

use App\Client\IpstackClient;
use App\Dto\IpsRequest;
use App\Entity\IpBlacklist;
use App\Repository\IpBlacklistRepository;
use App\Service\Helper\IpPrefetchService;
use App\Service\IpAddress\IpAddressCommandService;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IpBlacklistCommandService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IpstackClient $ipstackClient,
        private readonly IpAddressCommandService $ipAddressCommandService,
        private readonly IpBlacklistRepository $ipBlacklistRepository,
        private readonly IpPrefetchService $ipPrefetchService
    ) {}

    public function createOne(string $ip): string
    {
        [$ipBlacklistMap, $ipAddressMap] =
            $this->ipPrefetchService->mapPrefetchedData([$ip]);

        $this->processIpCreation($ip, $ipBlacklistMap, $ipAddressMap, true);

        $this->entityManager->flush();

        return $ip;
    }

    public function createAll(IpsRequest $ipsRequest): array
    {
        [$ipBlacklistMap, $ipAddressMap] =
            $this->ipPrefetchService->mapPrefetchedData($ipsRequest->ips);

        $createdIpArray = [];
        foreach ($ipsRequest->ips as $ip) {
            $ipBlacklist = $this->processIpCreation($ip, $ipBlacklistMap, $ipAddressMap, false);

            if ($ipBlacklist !== null) {
                $createdIpArray[] = $ip;
            }
        }

        $this->entityManager->flush();

        return $createdIpArray;
    }

    /**
     * @param array $ipBlacklistMap
     * @param array $ipAddressMap
     * @return IpBlacklist|null
     */
    private function processIpCreation(
        string $ip,
        array $ipBlacklistMap,
        array $ipAddressMap,
        bool $failOnExisting
    ): ?IpBlacklist
    {
        if (isset($ipBlacklistMap[$ip])) {
            if (true === $failOnExisting) {
                throw new RuntimeException("Ip {$ip} is already blacklisted");
            }
            return null;
        }

        $ipAddress = $ipAddressMap[$ip] ?? $this->ipAddressCommandService
            ->create($ip, $this->ipstackClient->getIpData($ip));

        $ipBlacklist = (new IpBlacklist())->setIpAddress($ipAddress);

        $this->entityManager->persist($ipBlacklist);

        return $ipBlacklist;
    }

    public function deleteOne(string $ip): void
    {
        $this->processIpDeletion([$ip]);
    }

    public function deleteAll(IpsRequest $ipsRequest): void
    {
        $this->processIpDeletion($ipsRequest->ips);
    }

    /**
     * @param string[] $ips
     */
    private function processIpDeletion(array $ips): void
    {
        $ipBlacklistArray = $this->ipBlacklistRepository->findAllByIp($ips);

        if ([] === $ipBlacklistArray) {
            throw new NotFoundHttpException(
                'Ip addresses not found: ' . implode(', ', $ips)
            );
        }

        foreach ($ipBlacklistArray as $ipBlacklist) {
            $this->entityManager->remove($ipBlacklist);
        }

        $this->entityManager->flush();
    }
}
