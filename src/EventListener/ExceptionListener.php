<?php

declare(strict_types=1);

namespace App\EventListener;

use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        switch (true) {
            case $exception instanceof AccessDeniedHttpException:
                $event->setResponse(
                    new JsonResponse([
                        'error' => 'Forbidden',
                        'message' => $exception->getMessage()
                    ], Response::HTTP_FORBIDDEN)
                );
                break;

            case $exception instanceof RuntimeException:
                $event->setResponse(
                    new JsonResponse([
                        'error' => 'Upstream API Error',
                        'message' => $exception->getMessage()
                    ], Response::HTTP_BAD_GATEWAY)
                );
                break;

            case $exception instanceof NotFoundHttpException:
                $event->setResponse(
                    new JsonResponse([
                        'error' => 'Not Found',
                    ], Response::HTTP_NOT_FOUND)
                );
                break;

            case $exception instanceof ValidationFailedException:
                $violations = [];
                foreach ($exception->getViolations() as $violation) {
                    $violations[] = [
                        'field' => $violation->getPropertyPath(),
                        'message' => $violation->getMessage()
                    ];
                }

                $event->setResponse(
                    new JsonResponse([
                        'error' => 'Validation Failed',
                        'violations' => $violations
                    ], Response::HTTP_BAD_REQUEST)
                );
                break;

            default:
                $event->setResponse(
                    new JsonResponse([
                        'error' => 'Internal Server Error',
                        'message' => $exception->getMessage()
                    ], Response::HTTP_INTERNAL_SERVER_ERROR)
                );
                break;
        }
    }
}
