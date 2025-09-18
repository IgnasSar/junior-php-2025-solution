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

#[Route('/api/ip-blacklist')]
class IpBlacklistController {
    public function __construct(
        private readonly IpBlacklistCommandService $ipBlacklistCommandService
    ){}

    #[Route(name: 'ip_blacklist_create', methods: ['POST'])]
    #[OA\Post(
        path: "/api/ip-blacklist",
        summary: "Add ip's to the blacklist",
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
                response: 201,
                description: "Ip's successfully added"
            )
        ]
    )]
    public function create(
        #[MapRequestPayload()] IpsRequest $ipsRequest
    ): JsonResponse
    {
        return new JsonResponse(
            $this->ipBlacklistCommandService->create($ipsRequest),
            Response::HTTP_CREATED
        );
    }

    #[Route(name: 'ip_blacklist_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/ip-blacklist",
        summary: "Remove ip's from the blacklist",
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
        $this->ipBlacklistCommandService->delete($ipsRequest);

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
