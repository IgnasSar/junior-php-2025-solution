<?php

declare(strict_types=1);

namespace App\Service\IpAddress;

use App\Dto\IpsRequest;
use App\Entity\IpAddress;
use App\Repository\IpAddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IpAddressCommandService
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IpAddressRepository $ipAddressRepository
    )
    {}

    public function update(IpAddress $ipAddress, array $fetchedIpData): IpAddress
    {
        $ipAddress->setData($fetchedIpData);

        $this->entityManager->flush();

        return $ipAddress;
    }

    public function create(string $ip, array $fetchedIpData): IpAddress
    {
        $ipAddress = (new IpAddress())
            ->setIp($ip)
            ->setData($fetchedIpData);

        $this->entityManager->persist($ipAddress);
        $this->entityManager->flush();

        return $ipAddress;
    }

    public function delete(IpsRequest $ipsRequest): void
    {
        $ipAddressArray = $this->ipAddressRepository
            ->findAllByIp($ipsRequest->ips);

        if([] === $ipAddressArray) {
            throw new NotFoundHttpException('Ip addresses not found');
        }

        foreach ($ipAddressArray as $ipAddress){
            $this->entityManager->remove($ipAddress);
        }

        $this->entityManager->flush();
    }
}
