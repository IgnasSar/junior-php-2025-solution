<?php

declare(strict_types=1);

namespace App\Tests\Service\IpAddress;

use App\Dto\IpsRequest;
use App\Entity\IpAddress;
use App\Repository\IpAddressRepository;
use App\Service\IpAddress\IpAddressCommandService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IpAddressCommandServiceTest extends TestCase
{
    public function testCreate(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(IpAddressRepository::class);

        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $service = new IpAddressCommandService($em, $repo);

        $ip = $service->create('1.1.1.1', ['metadata' => 'fetched']);

        $this->assertInstanceOf(IpAddress::class, $ip);
        $this->assertSame('1.1.1.1', $ip->getIp());
        $this->assertSame(['metadata' => 'fetched'], $ip->getData());
    }

    public function testUpdate(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(IpAddressRepository::class);

        $em->expects($this->once())->method('flush');

        $service = new IpAddressCommandService($em, $repo);

        $ipAddress = (new IpAddress())->setIp('1.1.1.1')->setData(['metadata' => 'fetched']);

        $updatedIp = $service->update($ipAddress, ['metadata' => 'fetched']);

        $this->assertSame($ipAddress, $updatedIp);
        $this->assertSame(['metadata' => 'fetched'], $updatedIp->getData());
    }

    public function testDelete(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(IpAddressRepository::class);
        $repo->method('findAllByIp')->willReturn([]);

        $service = new IpAddressCommandService($em, $repo);

        $this->expectException(NotFoundHttpException::class);

        $service->delete(new IpsRequest(['1.1.1.1']));
    }
}
