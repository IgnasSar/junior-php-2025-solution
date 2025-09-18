<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Dto\IpsRequest;
use OpenApi\Attributes as OA;
use App\Service\IpAddress\IpAddressCommandService;
use App\Service\IpAddress\IpAddressQueryService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ip-addresses')]
class IpAddressController {
    public function __construct(
        private readonly IpAddressQueryService $ipAddressQueryService,
        private readonly IpAddressCommandService $ipAddressCommandService,
    ){}

    #[Route(name: 'ip_address_get_collection', methods: ['GET'])]
    #[OA\Get(
        path: "/api/ip-addresses",
        summary: "Get information about ip's",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "ips",
                        type: "array",
                        items: new OA\Items(type: "string", example: "1.1.1.1")
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Ip data returned"
            )
        ]
    )]
    public function getCollection(
        #[MapRequestPayload] IpsRequest $ipsRequest
    ): JsonResponse
    {
        return new JsonResponse(
            $this->ipAddressQueryService->getAll($ipsRequest),
            Response::HTTP_OK
        );
    }

    #[Route(name: 'ip_address_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/ip-addresses",
        summary: "Remove ip's from database",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "ips",
                        type: "array",
                        items: new OA\Items(type: "string", example: "1.1.1.1")
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Ip's successfully removed"
            )
        ]
    )]
    public function delete(
        #[MapRequestPayload] IpsRequest $ipsRequest
    ): JsonResponse
    {
        $this->ipAddressCommandService->delete($ipsRequest);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
