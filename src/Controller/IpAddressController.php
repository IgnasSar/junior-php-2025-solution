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

#[Route('/ip-addresses')]
class IpAddressController {
    public function __construct(
        private readonly IpAddressQueryService $ipAddressQueryService,
        private readonly IpAddressCommandService $ipAddressCommandService
    ){}

    #[Route(name: 'ip_address_get_collection', methods: ['GET'])]
    #[OA\Get(
        path: "/api/ip-addresses",
        summary: "Get information about ip addresses in collection",
        requestBody: new OA\RequestBody(
            required: true,
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
                description: "Ip address data in collection format returned",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(type: "object")
                )
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

    #[Route('/{ip}', name: 'ip_address_get_one', methods: ['GET'])]
    #[OA\Get(
        path: "/api/ip-addresses/{ip}",
        summary: "Get information about a single ip address",
        parameters: [
            new OA\Parameter(
                name: "ip",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", example: "1.1.1.1")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Ip address data returned",
                content: new OA\JsonContent(type: "object")
            ),
            new OA\Response(
                response: 404,
                description: "Ip address not found"
            )
        ]
    )]
    public function getOne(string $ip): JsonResponse
    {
        return new JsonResponse(
            $this->ipAddressQueryService->getOne($ip),
            Response::HTTP_OK
        );
    }

    #[Route(name: 'ip_address_delete_collection', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/ip-addresses",
        summary: "Remove ip address collection from database",
        requestBody: new OA\RequestBody(
            required: true,
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
                description: "Ip address collection successfully removed"
            )
        ]
    )]
    public function deleteCollection(
        #[MapRequestPayload] IpsRequest $ipsRequest
    ): JsonResponse
    {
        $this->ipAddressCommandService->deleteAll($ipsRequest);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/{ip}', name: 'ip_address_delete_one', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/ip-addresses/{ip}",
        summary: "Remove a single ip address from database",
        parameters: [
            new OA\Parameter(
                name: "ip",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "string", example: "1.1.1.1")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Ip address successfully removed"
            ),
            new OA\Response(
                response: 404,
                description: "Ip address not found"
            )
        ]
    )]
    public function deleteOne(string $ip): JsonResponse
    {
        $this->ipAddressCommandService->deleteOne($ip);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
