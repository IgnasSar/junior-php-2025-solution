<?php

declare(strict_types=1);

namespace App\Tests\Service\IpBlacklist;

use App\Client\IpstackClient;
use App\Dto\IpsRequest;
use App\Entity\IpAddress;
use App\Repository\IpAddressRepository;
use App\Repository\IpBlacklistRepository;
use App\Service\IpAddress\IpAddressCommandService;
use App\Service\IpBlacklist\IpBlacklistCommandService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IpBlacklistCommandServiceTest extends TestCase
{
    public function testCreate(): void
    {
        $ips = ['1.1.1.1', '2.2.2.2'];

        $em = $this->createMock(EntityManagerInterface::class);
        $ipAddressRepo = $this->createMock(IpAddressRepository::class);
        $ipBlacklistRepo = $this->createMock(IpBlacklistRepository::class);
        $ipstackClient = $this->createMock(IpstackClient::class);
        $ipAddressService = $this->createMock(IpAddressCommandService::class);

        $ipAddressRepo->method('findAllByIp')->willReturn([]);
        $ipBlacklistRepo->method('findAllByIp')->willReturn([]);

        $ipstackClient->method('getIpData')->willReturn(['metadata' => 'fetched']);

        $ipAddressService->method('create')
            ->willReturnCallback(fn($ip, $data) => (new IpAddress())->setIp($ip)->setData($data));

        $em->expects($this->exactly(2))->method('persist');
        $em->expects($this->once())->method('flush');

        $service = new IpBlacklistCommandService(
            $em,
            $ipAddressRepo,
            $ipstackClient,
            $ipAddressService,
            $ipBlacklistRepo
        );

        $createdIps = $service->create(new IpsRequest($ips));

        $this->assertSame($ips, $createdIps);
    }

    public function testDelete(): void
    {
        $repo = $this->createMock(IpBlacklistRepository::class);
        $repo->method('findAllByIp')->willReturn([]);

        $service = new IpBlacklistCommandService(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(IpAddressRepository::class),
            $this->createMock(IpstackClient::class),
            $this->createMock(IpAddressCommandService::class),
            $repo
        );

        $this->expectException(NotFoundHttpException::class);

        $service->delete(new IpsRequest(['1.1.1.1']));
    }
}
