<?php

namespace Test;

use Kovagoz\Http\Middleware\ClosureHandler\ClosureHandler;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ClosureHandlerTest extends TestCase
{
    public function testHappyPath(): void
    {
        // Request has the closure handler
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with('__handler')
            ->willReturn(fn() => 'hello world');

        // Handler of the next middleware should not be called
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $middleware = new ClosureHandler(new Psr17Factory(), new Psr17Factory());

        $response = $middleware->process($request, $handler);

        self::assertEquals('hello world', $response->getBody());
    }

    public function testChangeRequestAttribute(): void
    {
        // Request has the closure handler under a new key
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with('__request_handler')
            ->willReturn(fn() => 'hello world');

        // Handler of the next middleware should not be called
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $middleware = new ClosureHandler(new Psr17Factory(), new Psr17Factory());
        $middleware->watchRequestAttribute('__request_handler');

        $response = $middleware->process($request, $handler);

        self::assertEquals('hello world', $response->getBody());
    }

    public function testNoRequestHandlerDefined(): void
    {
        // Request does not have a handler attribute
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $request->expects(self::once())->method('getAttribute')->willReturn(null);

        // Response from the next middleware
        $response = $this->getMockForAbstractClass(ResponseInterface::class);

        // This is the next middleware in the stack
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->willReturn($response);

        $middleware = new ClosureHandler(new Psr17Factory(), new Psr17Factory());

        // Response from the next middleware should return
        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testRequestHandlerIsNotClosure(): void
    {
        // Request has handler attribute but it's not closure
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with('__handler')
            ->willReturn(123);

        // Response from the next middleware
        $response = $this->getMockForAbstractClass(ResponseInterface::class);

        // Next middleware in the stack should be called
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->willReturn($response);

        $middleware = new ClosureHandler(new Psr17Factory(), new Psr17Factory());

        // Response from the next middleware should return
        self::assertSame($response, $middleware->process($request, $handler));
    }
}
