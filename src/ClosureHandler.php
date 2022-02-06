<?php

namespace Kovagoz\Http\Middleware\ClosureHandler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * @see \Test\ClosureHandlerTest
 */
class ClosureHandler implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private StreamFactoryInterface   $streamFactory;
    private string                   $handlerAttribute = '__handler';

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
    }

    /**
     * Set the name of the request attribute which may hold the closure.
     *
     * @param string $attribute
     */
    public function watchRequestAttribute(string $attribute): void
    {
        $this->handlerAttribute = $attribute;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $requestHandler = $request->getAttribute($this->handlerAttribute);

        if ($requestHandler instanceof \Closure) {
            return $this->responseFactory->createResponse()->withBody(
                $this->streamFactory->createStream($requestHandler($request))
            );
        }

        return $handler->handle($request);
    }
}
