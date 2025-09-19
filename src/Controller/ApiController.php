<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/health', name: 'api_health', methods: ['GET'])]
    #[OA\Get(
        path: "/api/health",
        summary: "Health check",
        responses: [
            new OA\Response(
                response: 200,
                description: "API is healthy",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "ok")
                    ]
                )
            )
        ]
    )]
    public function health(): JsonResponse
    {
        return $this->json(['status' => 'ok']);
    }
}