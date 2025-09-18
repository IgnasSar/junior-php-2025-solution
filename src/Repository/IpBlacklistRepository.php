<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpBlacklist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class IpBlacklistRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $managerRegistry) {
        parent::__construct($managerRegistry, IpBlacklist::class);
    }

    /**
     * @param string[] $ips
     * @return IpBlacklist[]
     */
    public function findAllByIp(array $ips): array
    {
        return $this->createQueryBuilder('ib')
            ->join('ib.ipAddress', 'ia')
            ->andWhere('ia.ip IN (:ips)')
            ->setParameter('ips', $ips)
            ->getQuery()
            ->getResult();
    }
}
