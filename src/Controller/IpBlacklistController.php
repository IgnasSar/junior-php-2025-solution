<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Dto\IpsRequest;
use App\Service\IpBlacklist\IpBlacklistCommandService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/ip-blacklist')]
class IpBlacklistController {
    public function __construct(
        private readonly IpBlacklistCommandService $ipBlacklistCommandService
    ){}

    #[Route(name: 'ip_blacklist_create_collection', methods: ['POST'])]
    #[OA\Post(
        path: "/api/ip-blacklist",
        summary: "Add ip address collection to the blacklist",
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
                response: 201,
                description: "Ip addresses successfully added"
            ),
            new OA\Response(
                response: 400,
                description: "Invalid request payload"
            ),
            new OA\Response(
                response: 409,
                description: "One or more ip address already blacklisted"
            )
        ]
    )]
    public function createCollection(
        #[MapRequestPayload] IpsRequest $ipsRequest
    ): JsonResponse
    {
        return new JsonResponse(
            $this->ipBlacklistCommandService->createAll($ipsRequest),
            Response::HTTP_CREATED
        );
    }

    #[Route('/{ip}', name: 'ip_blacklist_create_one', methods: ['POST'])]
    #[OA\Post(
        path: "/api/ip-blacklist/{ip}",
        summary: "Add a single ip address to the blacklist",
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
                response: 201,
                description: "Ip address successfully added"
            ),
            new OA\Response(
                response: 409,
                description: "Ip address already blacklisted"
            )
        ]
    )]
    public function createOne(
        string $ip
    ): JsonResponse
    {
        return new JsonResponse(
            $this->ipBlacklistCommandService->createOne($ip),
            Response::HTTP_CREATED
        );
    }

    #[Route(name: 'ip_blacklist_delete_collection', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/ip-blacklist",
        summary: "Remove ip address collection from the blacklist",
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
                description: "Ip addresses successfully removed"
            ),
            new OA\Response(
                response: 404,
                description: "One or more ip address not found"
            )
        ]
    )]
    public function deleteCollection(
        #[MapRequestPayload] IpsRequest $ipsRequest
    ): JsonResponse
    {
        $this->ipBlacklistCommandService->deleteAll($ipsRequest);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/{ip}', name: 'ip_blacklist_delete_one', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/ip-blacklist/{ip}",
        summary: "Remove a single ip address from the blacklist",
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
    public function deleteOne(
        string $ip
    ): JsonResponse
    {
        $this->ipBlacklistCommandService->deleteOne($ip);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
