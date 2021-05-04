<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Request\RequestAttributes;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class ZoneMatcherListener
{
    private RequestMatcherInterface $requestMatcher;

    public function __construct(RequestMatcherInterface $requestMatcher)
    {
        $this->requestMatcher = $requestMatcher;
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $matched = $this->requestMatcher->matches($request);

        $request->attributes->set(RequestAttributes::API_ZONE, $matched);
    }
}
