<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class IpAddressRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $managerRegistry) {
        parent::__construct($managerRegistry, IpAddress::class);
    }

    /**
     * Fetch multiple IpAddress entities by provided IP values.
     *
     * @param string[] $ips
     * @return IpAddress[]
     */
    public function findAllByIp(array $ips): array
    {
        return $this->createQueryBuilder('ia')
            ->andWhere('ia.ip IN (:ips)')
            ->setParameter('ips', $ips)
            ->getQuery()
            ->getResult();
    }
}
