<?php

declare(strict_types=1);

namespace App\Service\IpBlacklist;

use App\Dto\IpsRequest;
use App\Entity\IpAddress;
use App\Entity\IpBlacklist;
use App\Repository\IpBlacklistRepository;
use App\Service\Helper\IpPrefetchService;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IpBlacklistCommandService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IpBlacklistRepository $ipBlacklistRepository,
        private readonly IpPrefetchService $ipPrefetchService
    ) {}

    /**
     * Create a single blacklist entry for an IP.
     * Validates IP format and throws BadRequestHttpException if invalid.
     * Throws RuntimeException if IP is already blacklisted.
     */
    public function createOne(string $ip): string
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new BadRequestHttpException("Invalid IP address: {$ip}");
        }

        [$ipBlacklistMap, $ipAddressMap] =
            $this->ipPrefetchService->mapPrefetchedData([$ip]);

        $this->processIpCreation($ip, $ipBlacklistMap, $ipAddressMap, true);

        $this->entityManager->flush();

        return $ip;
    }

    /**
     * Bulk create blacklist entries for multiple IPs.
     *
     * If all provided IPs dont match ipAddress map or
     * all of them are IPs matching ipBlacklist map criteria
     *  - NotFoundHttpException will be thrown
     */
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

        if ([] === $createdIpArray) {
            throw new NotFoundHttpException(
                'Ip addresses not found: ' . implode(', ', $ipsRequest->ips)
            );
        }

        $this->entityManager->flush();

        return $createdIpArray;
    }

    /**
     * Handles the creation of a single IpBlacklist entity.
     * Skips already blacklisted IPs.
     *
     * If no ipAdress->ip matches passed down $ip,
     * either NotFoundHttpException will be thrown for creating one object
     * or it will return null until the end of array
     *
     * @param IpBlacklist[] $ipBlacklistMap
     * @param IpAddress[] $ipAddressMap
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

        $ipAddress = $ipAddressMap[$ip] ?? null;

        if(null === $ipAddress){
            if(true === $failOnExisting) {
                throw new NotFoundHttpException("Ip address instance with {$ip} is not found");
            }
            return null;
        }

        $ipBlacklist = (new IpBlacklist())->setIpAddress($ipAddress);

        $this->entityManager->persist($ipBlacklist);

        return $ipBlacklist;
    }

    /**
     * Delete a single IP from blacklist.
     */
    public function deleteOne(string $ip): void
    {
        $this->processIpDeletion([$ip]);
    }


    /**
     * Bulk delete multiple IpBlacklist objects.
     */
    public function deleteAll(IpsRequest $ipsRequest): void
    {
        $this->processIpDeletion($ipsRequest->ips);
    }

    /**
     * Core deletion logic for one or more IPs.
     * Throws NotFoundHttpException if no matching entries are found.
     *
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
