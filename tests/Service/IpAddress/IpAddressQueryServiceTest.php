<?php

declare(strict_types=1);

namespace App\Tests\Service\IpAddress;

use App\Client\IpstackClient;
use App\Dto\IpsRequest;
use App\Entity\IpAddress;
use App\Entity\IpBlacklist;
use App\Normalizer\IpAddressNormalizer;
use App\Repository\IpAddressRepository;
use App\Repository\IpBlacklistRepository;
use App\Service\IpAddress\IpAddressCommandService;
use App\Service\IpAddress\IpAddressQueryService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class IpAddressQueryServiceTest extends TestCase
{
    public function testGetAllThrowsWhenBlacklisted(): void
    {
        $ipAddressMock = $this->createMock(IpAddress::class);
        $ipAddressMock->method('getIp')->willReturn('1.1.1.1');

        $blacklistItemMock = $this->createMock(IpBlacklist::class);
        $blacklistItemMock->method('getIpAddress')->willReturn($ipAddressMock);

        $blacklistRepo = $this->createMock(IpBlacklistRepository::class);
        $blacklistRepo->method('findAllByIp')->willReturn([$blacklistItemMock]);

        $service = new IpAddressQueryService(
            $this->createMock(IpAddressRepository::class),
            new IpAddressNormalizer(),
            $this->createMock(IpstackClient::class),
            $this->createMock(IpAddressCommandService::class),
            $blacklistRepo
        );

        $this->expectException(AccessDeniedHttpException::class);

        $service->getAll(new IpsRequest(['1.1.1.1']));
    }

    public function testGetAll(): void
    {
        $repo = $this->createMock(IpAddressRepository::class);
        $repo->method('findAllByIp')->willReturn([]);

        $ipstack = $this->createMock(IpstackClient::class);
        $ipstack->method('getIpData')->willReturn(['ip' => '1.1.1.1']);

        $commandService = $this->createMock(IpAddressCommandService::class);
        $ip = (new IpAddress())->setIp('1.1.1.1')->setData(['ip' => '1.1.1.1']);
        $commandService->method('create')->willReturn($ip);

        $service = new IpAddressQueryService(
            $repo,
            new IpAddressNormalizer(),
            $ipstack,
            $commandService,
            $this->createMock(IpBlacklistRepository::class)
        );

        $result = $service->getAll(new IpsRequest(['1.1.1.1']));

        $this->assertSame([['id' => null, 'data' => ['ip' => '1.1.1.1']]], $result);
    }
}
