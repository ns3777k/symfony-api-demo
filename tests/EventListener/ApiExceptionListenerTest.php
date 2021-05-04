<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\ApiExceptionListener;
use App\Model\ErrorResponse;
use App\Request\RequestAttributes;
use App\Service\System\ExceptionMapping;
use App\Service\System\ExceptionMappingResolver;
use App\Tests\AbstractTestCase;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

class ApiExceptionListenerTest extends AbstractTestCase
{
    private function createTestKernel(): HttpKernelInterface
    {
        return new class() implements HttpKernelInterface {
            public function handle(Request $request, int $type = self::MASTER_REQUEST, bool $catch = true)
            {
                return new Response('test');
            }
        };
    }

    private function createEvent(Throwable $e, bool $apiZone = true): ExceptionEvent
    {
        $request = new Request([], [], [RequestAttributes::API_ZONE => $apiZone]);

        return new ExceptionEvent(
            $this->createTestKernel(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $e
        );
    }

    public function testIgnoresNonApiZone(): void
    {
        $mapper = $this->createMock(ExceptionMappingResolver::class);
        $logger = $this->createMock(LoggerInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $event = $this->createEvent(new Exception('test'), false);

        $listener = new ApiExceptionListener($mapper, $logger, $serializer);
        $listener($event);

        $this->assertNull($event->getResponse());
    }

    public function testNon500MappingWithHiddenMessage(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->with(new ErrorResponse('Not Found'), JsonEncoder::FORMAT)
            ->willReturn('{"error": "Not Found"}');

        $mapping = new ExceptionMapping(Response::HTTP_NOT_FOUND);
        $mapper = $this->createMock(ExceptionMappingResolver::class);
        $mapper->expects($this->once())
            ->method('resolve')
            ->with(InvalidArgumentException::class)
            ->willReturn($mapping);

        $event = $this->createEvent(new InvalidArgumentException('test'));
        $listener = new ApiExceptionListener($mapper, $logger, $serializer);
        $listener($event);

        /** @var Response $response */
        $response = $event->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);

        $errorMessage = (string) json_encode(['error' => Response::$statusTexts[$mapping->getCode()]]);
        $this->assertJsonStringEqualsJsonString($errorMessage, (string) $response->getContent());
    }

    public function testNon500MappingWithPublicMessage(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->with(new ErrorResponse('test'), JsonEncoder::FORMAT)
            ->willReturn('{"error": "test"}');

        $mapping = new ExceptionMapping(Response::HTTP_NOT_FOUND, false);
        $mapper = $this->createMock(ExceptionMappingResolver::class);
        $mapper->expects($this->once())
            ->method('resolve')
            ->with(InvalidArgumentException::class)
            ->willReturn($mapping);

        $event = $this->createEvent(new InvalidArgumentException('test'));
        $listener = new ApiExceptionListener($mapper, $logger, $serializer);
        $listener($event);

        /** @var Response $response */
        $response = $event->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJsonStringEqualsJsonString('{"error": "test"}', (string) $response->getContent());
    }

    public function testNon500LoggableMappingTriggersLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('test', $this->anything());

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->with(new ErrorResponse('test'), JsonEncoder::FORMAT)
            ->willReturn('{"error": "test"}');

        $mapping = new ExceptionMapping(Response::HTTP_NOT_FOUND, false, true);
        $mapper = $this->createMock(ExceptionMappingResolver::class);
        $mapper->expects($this->once())
            ->method('resolve')
            ->with(InvalidArgumentException::class)
            ->willReturn($mapping);

        $event = $this->createEvent(new InvalidArgumentException('test'));
        $listener = new ApiExceptionListener($mapper, $logger, $serializer);
        $listener($event);

        /** @var Response $response */
        $response = $event->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJsonStringEqualsJsonString('{"error": "test"}', (string) $response->getContent());
    }

    public function test500IsLoggable(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('error message', $this->anything());

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->with(new ErrorResponse('Gateway Timeout'), JsonEncoder::FORMAT)
            ->willReturn('{"error": "Gateway Timeout"}');

        $mapping = new ExceptionMapping(Response::HTTP_GATEWAY_TIMEOUT);
        $mapper = $this->createMock(ExceptionMappingResolver::class);
        $mapper->expects($this->once())
            ->method('resolve')
            ->with(InvalidArgumentException::class)
            ->willReturn($mapping);

        $event = $this->createEvent(new InvalidArgumentException('error message'));
        $listener = new ApiExceptionListener($mapper, $logger, $serializer);
        $listener($event);

        /** @var Response $response */
        $response = $event->getResponse();
        $this->assertEquals(Response::HTTP_GATEWAY_TIMEOUT, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);

        $errorMessage = (string) json_encode(['error' => Response::$statusTexts[$mapping->getCode()]]);
        $this->assertJsonStringEqualsJsonString($errorMessage, (string) $response->getContent());
    }

    public function test500IsDefaultWhenMappingNotFound(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('error message', $this->anything());

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->with(new ErrorResponse('Internal Server Error'), JsonEncoder::FORMAT)
            ->willReturn('{"error": "Internal Server Error"}');

        $mapper = $this->createMock(ExceptionMappingResolver::class);
        $mapper->expects($this->once())
            ->method('resolve')
            ->with(InvalidArgumentException::class)
            ->willReturn(null);

        $event = $this->createEvent(new InvalidArgumentException('error message'));
        $listener = new ApiExceptionListener($mapper, $logger, $serializer);
        $listener($event);

        /** @var Response $response */
        $response = $event->getResponse();
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);

        $errorMessage = (string) json_encode(['error' => Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]]);
        $this->assertJsonStringEqualsJsonString($errorMessage, (string) $response->getContent());
    }
}
