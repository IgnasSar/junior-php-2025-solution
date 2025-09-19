<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\IpBlacklistController;
use App\Dto\IpsRequest;
use App\Service\IpBlacklist\IpBlacklistCommandService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use RuntimeException;

class IpBlacklistControllerTest extends TestCase
{
    public function testCreateOne(): void
    {
        $ipBlacklistCommandService = $this->createMock(IpBlacklistCommandService::class);
        $ipBlacklistCommandService
            ->method('createOne')
            ->willReturn('1.2.3.4');

        $controller = new IpBlacklistController($ipBlacklistCommandService);

        $response = $controller->createOne('1.2.3.4');

        $this->assertSame(JsonResponse::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals(json_encode('1.2.3.4'), $response->getContent());
    }

    public function testCreateOneAlreadyBlacklisted(): void
    {
        $this->expectException(RuntimeException::class);

        $ipBlacklistCommandService = $this->createMock(IpBlacklistCommandService::class);
        $ipBlacklistCommandService
            ->method('createOne')
            ->willThrowException(new RuntimeException());

        $controller = new IpBlacklistController($ipBlacklistCommandService);

        $controller->createOne('1.2.3.4');
    }

    public function testCreateCollection(): void
    {
        $ipsRequest = new IpsRequest(['1.1.1.1', '2.2.2.2']);

        $ipBlacklistCommandService = $this->createMock(IpBlacklistCommandService::class);
        $ipBlacklistCommandService
            ->method('createAll')
            ->willReturn($ipsRequest->ips);

        $controller = new IpBlacklistController($ipBlacklistCommandService);

        $response = $controller->createCollection($ipsRequest);

        $this->assertSame(JsonResponse::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals(json_encode($ipsRequest->ips), $response->getContent());
    }

    public function testDeleteOne(): void
    {
        $ipBlacklistCommandService = $this->createMock(IpBlacklistCommandService::class);
        $ipBlacklistCommandService
            ->expects($this->once())
            ->method('deleteOne');

        $controller = new IpBlacklistController($ipBlacklistCommandService);

        $response = $controller->deleteOne('1.2.3.4');

        $this->assertSame(JsonResponse::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testDeleteOneNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $ipBlacklistCommandService = $this->createMock(IpBlacklistCommandService::class);
        $ipBlacklistCommandService
            ->method('deleteOne')
            ->willThrowException(new NotFoundHttpException());

        $controller = new IpBlacklistController($ipBlacklistCommandService);

        $controller->deleteOne('1.2.3.4');
    }
}
