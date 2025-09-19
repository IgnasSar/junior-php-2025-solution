<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\IpAddressController;
use App\Dto\IpsRequest;
use App\Service\IpAddress\IpAddressCommandService;
use App\Service\IpAddress\IpAddressQueryService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IpAddressControllerTest extends TestCase
{
    public function testGetOne(): void
    {
        $ipAddressqueryService = $this->createMock(IpAddressQueryService::class);
        $ipAddressqueryService->method('getOne')->willReturn(['some' => 'data']);

        $controller = new IpAddressController(
            $ipAddressqueryService,
            $this->createMock(IpAddressCommandService::class)
        );

        $response = $controller->getOne('1.2.3.4');

        $this->assertSame(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(json_encode(['some' => 'data']), $response->getContent());
    }

    public function testGetOneBlacklisted(): void
    {
        $this->expectException(AccessDeniedHttpException::class);

        $ipAddressqueryService = $this->createMock(IpAddressQueryService::class);
        $ipAddressqueryService->method('getOne')->willThrowException(new AccessDeniedHttpException());

        $controller = new IpAddressController(
            $ipAddressqueryService,
            $this->createMock(IpAddressCommandService::class)
        );

        $controller->getOne('1.2.3.4');
    }

    public function testGetCollection(): void
    {
        $ipsRequest = new IpsRequest(['1.1.1.1', '2.2.2.2']);

        $ipAddressQueryService = $this->createMock(IpAddressQueryService::class);
        $ipAddressQueryService->method('getAll')->willReturn([['a' => 'b'], ['x' => 'y']]);

        $controller = new IpAddressController(
            $ipAddressQueryService,
            $this->createMock(IpAddressCommandService::class)
        );

        $response = $controller->getCollection($ipsRequest);

        $this->assertSame(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(json_encode([['a' => 'b'], ['x' => 'y']]), $response->getContent());
    }

    public function testDeleteOne(): void
    {
        $ipAddressCommandService = $this->createMock(IpAddressCommandService::class);
        $ipAddressCommandService->expects($this->once())->method('deleteOne');

        $controller = new IpAddressController(
            $this->createMock(IpAddressQueryService::class),
            $ipAddressCommandService
        );

        $response = $controller->deleteOne('1.2.3.4');

        $this->assertSame(JsonResponse::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testDeleteOneNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $ipAddressCommandService = $this->createMock(IpAddressCommandService::class);
        $ipAddressCommandService->method('deleteOne')
            ->willThrowException(new NotFoundHttpException());

        $controller = new IpAddressController(
            $this->createMock(IpAddressQueryService::class),
            $ipAddressCommandService
        );

        $controller->deleteOne('1.2.3.4');
    }
}
