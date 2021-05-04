<?php

declare(strict_types=1);

namespace App\Tests\Service\System;

use App\Service\System\ExceptionMapping;
use App\Service\System\ExceptionMappingResolver;
use App\Tests\AbstractTestCase;
use InvalidArgumentException;
use LogicException;

class ExceptionMappingResolverTest extends AbstractTestCase
{
    public function testResolveThrowsExceptionOnEmptyCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/code is mandatory for class/');

        new ExceptionMappingResolver(['someClass' => ['hidden' => true]]);
    }

    public function testResolvesToNullWhenNotFound(): void
    {
        $resolver = new ExceptionMappingResolver([]);
        $this->assertNull($resolver->resolve(InvalidArgumentException::class));
    }

    public function testResolvesClassItself(): void
    {
        $resolver = new ExceptionMappingResolver([
            InvalidArgumentException::class => ['code' => 400],
        ]);

        /** @var ExceptionMapping $mapping */
        $mapping = $resolver->resolve(InvalidArgumentException::class);
        $this->assertEquals(400, $mapping->getCode());
    }

    public function testResolvesSubClass(): void
    {
        $resolver = new ExceptionMappingResolver([
            LogicException::class => ['code' => 500],
        ]);

        /** @var ExceptionMapping $mapping */
        $mapping = $resolver->resolve(InvalidArgumentException::class);
        $this->assertEquals(500, $mapping->getCode());
    }

    public function testResolvesHidden(): void
    {
        $resolver = new ExceptionMappingResolver([
            LogicException::class => ['code' => 500, 'hidden' => false],
        ]);

        /** @var ExceptionMapping $mapping */
        $mapping = $resolver->resolve(InvalidArgumentException::class);
        $this->assertFalse($mapping->isHidden());
    }

    public function testResolvesLoggable(): void
    {
        $resolver = new ExceptionMappingResolver([
            LogicException::class => ['code' => 500, 'loggable' => true],
        ]);

        /** @var ExceptionMapping $mapping */
        $mapping = $resolver->resolve(InvalidArgumentException::class);
        $this->assertTrue($mapping->isLoggable());
    }
}
