<?php

declare(strict_types=1);

namespace App\Service\IpBlacklist;

use App\Client\IpstackClient;
use App\Dto\IpsRequest;
use App\Entity\IpBlacklist;
use App\Repository\IpAddressRepository;
use App\Repository\IpBlacklistRepository;
use App\Service\IpAddress\IpAddressCommandService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IpBlacklistCommandService{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IpAddressRepository $ipAddressRepository,
        private readonly IpstackClient $ipstackClient,
        private readonly IpAddressCommandService $ipAddressCommandService,
        private readonly IpBlacklistRepository $ipBlacklistRepository
    ){}

    public function create(IpsRequest $ipsRequest): array
    {
        [$ipAddressMap, $ipBlacklistMap] = $this->buildIpMaps($ipsRequest->ips);

        $createdIps = [];

        foreach ($ipsRequest->ips as $ip) {
            if (isset($ipBlacklistMap[$ip])) {
                continue;
            }

            $ipAddress = $ipAddressMap[$ip] ?? null;
            if ($ipAddress === null) {
                $ipAddress = $this->ipAddressCommandService
                    ->create($ip, $this->ipstackClient->getIpData($ip));
            }

            $ipBlacklist = (new IpBlacklist())->setIpAddress($ipAddress);

            $this->entityManager->persist($ipBlacklist);

            $createdIps[] = $ip;
        }

        $this->entityManager->flush();

        return $createdIps;
    }

    /**
     * @param string[] $ips
     * @return array[]
     */
    private function buildIpMaps(array $ips): array
    {
        $existingIpAddresses = $this->ipAddressRepository->findAllByIp($ips);

        $ipAddressMap = [];
        foreach ($existingIpAddresses as $ipAddress) {
            $ipAddressMap[$ipAddress->getIp()] = $ipAddress;
        }

        $existingBlacklists = $this->ipBlacklistRepository->findAllByIp($ips);

        $ipBlacklistMap = [];
        foreach ($existingBlacklists as $ipBlacklist) {
            $ipBlacklistMap[$ipBlacklist->getIpAddress()->getIp()] = true;
        }

        return [$ipAddressMap, $ipBlacklistMap];
    }


    public function delete(IpsRequest $ipsRequest): void
    {
        $ipBlacklistArray = $this->ipBlacklistRepository
            ->findAllByIp($ipsRequest->ips);

        if([] === $ipBlacklistArray) {
            throw new NotFoundHttpException(
                'Ip addresses not found: ' . implode(', ', $ipsRequest->ips)
            );
        }

        foreach ($ipBlacklistArray as $ipBlacklist) {
            $this->entityManager->remove($ipBlacklist);
        }

        $this->entityManager->flush();
    }
}
