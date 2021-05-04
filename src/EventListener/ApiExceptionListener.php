<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Model\ErrorResponse;
use App\Request\RequestAttributes;
use App\Service\System\ExceptionMapping;
use App\Service\System\ExceptionMappingResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class ApiExceptionListener
{
    private LoggerInterface $logger;

    private ExceptionMappingResolver $mappingResolver;

    private SerializerInterface $serializer;

    public function __construct(
        ExceptionMappingResolver $mappingResolver,
        LoggerInterface $logger,
        SerializerInterface $serializer
    ) {
        $this->mappingResolver = $mappingResolver;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $isApiZone = $event->getRequest()->attributes->get(RequestAttributes::API_ZONE, true);
        if (!$isApiZone) {
            return;
        }

        $throwable = $event->getThrowable();
        $mapping = $this->mappingResolver->resolve(\get_class($throwable));
        if (null === $mapping) {
            $mapping = new ExceptionMapping(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($mapping->getCode() >= Response::HTTP_INTERNAL_SERVER_ERROR || $mapping->isLoggable()) {
            $this->logger->error($throwable->getMessage(), [
                'trace' => $throwable->getTraceAsString(),
                'previous' => null !== $throwable->getPrevious() ? $throwable->getPrevious()->getMessage() : '',
            ]);
        }

        $message = $mapping->isHidden() ? Response::$statusTexts[$mapping->getCode()] : $throwable->getMessage();
        $data = $this->serializer->serialize(new ErrorResponse($message), JsonEncoder::FORMAT);
        $response = new JsonResponse($data, $mapping->getCode(), [], true);
        $event->setResponse($response);
    }
}
