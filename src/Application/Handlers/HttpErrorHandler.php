<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Application\Handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Throwable;

class HttpErrorHandler extends SlimErrorHandler
{
    /**
     * @inheritdoc
     */
    protected function respond(): Response
    {
        $exception = $this->exception;
        $statusCode = 500;
        $payload = [];

        $this->logger->error($exception->getMessage());

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $payload['message'] = $exception->getMessage();
        }

        if (
            !($exception instanceof HttpException)
            && $exception instanceof Throwable
            && $this->displayErrorDetails
        ) {
            $payload['message'] = $exception->getMessage();
        }

        $encodedPayload = json_encode($payload, JSON_PRETTY_PRINT);

        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write($encodedPayload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
