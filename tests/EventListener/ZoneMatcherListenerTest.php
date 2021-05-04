<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\ZoneMatcherListener;
use App\Request\RequestAttributes;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ZoneMatcherListenerTest extends AbstractTestCase
{
    public function testSetsTrueAttributeOnMatch(): void
    {
        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel, $request, null);
        $requestMatcher = $this->createMock(RequestMatcherInterface::class);
        $requestMatcher->expects($this->once())
            ->method('matches')
            ->with($request)
            ->willReturn(true);

        (new ZoneMatcherListener($requestMatcher))($requestEvent);

        $this->assertTrue($request->attributes->get(RequestAttributes::API_ZONE));
    }

    public function testSetsFalseAttributeOnMatch(): void
    {
        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel, $request, null);
        $requestMatcher = $this->createMock(RequestMatcherInterface::class);
        $requestMatcher->expects($this->once())
            ->method('matches')
            ->with($request)
            ->willReturn(false);

        (new ZoneMatcherListener($requestMatcher))($requestEvent);

        $this->assertFalse($request->attributes->get(RequestAttributes::API_ZONE));
    }
}
