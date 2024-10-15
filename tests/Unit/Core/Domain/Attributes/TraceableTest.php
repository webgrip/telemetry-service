<?php

namespace Unit\Core\Domain\Attributes;

use ReflectionClass;
use ReflectionMethod;
use Webgrip\TelemetryService\Core\Domain\Attributes\Traceable;
use Webgrip\TelemetryService\Tests\Fixtures\NonTraceableClass;
use Webgrip\TelemetryService\Tests\Fixtures\PartiallyTraceableClass;
use Webgrip\TelemetryService\Tests\Fixtures\TraceableClass;
use Webgrip\TelemetryService\Tests\Unit\TestCase;

class TraceableTest extends TestCase
{
    public function testClassHasTraceableAttribute()
    {
        $reflection = new ReflectionClass(TraceableClass::class);
        $attributes = $reflection->getAttributes(Traceable::class);

        $this->assertNotEmpty($attributes, "Class 'ExampleClass' should have Traceable attribute");
    }

    public function testMethodHasTraceableAttribute()
    {
        $partiallyTracableClass = new ReflectionClass(PartiallyTraceableClass::class);
        $tracedReflection = new ReflectionMethod(PartiallyTraceableClass::class, 'tracedMethod');
        $untracedReflection = new ReflectionMethod(PartiallyTraceableClass::class, 'untracedMethod');;

        $this->assertEmpty(
            $partiallyTracableClass->getAttributes(Traceable::class),
            "Method 'tracedMethod' should have Traceable attribute"
        );

        $this->assertNotEmpty(
            $tracedReflection->getAttributes(Traceable::class),
            "Method 'tracedMethod' should have Traceable attribute"
        );
        $this->assertEmpty(
            $untracedReflection->getAttributes(Traceable::class),
            "Method 'tracedMethod' should have Traceable attribute"
        );
    }

    public function testMethodWithoutTraceableAttribute()
    {
        $reflection = new ReflectionMethod(NonTraceableClass::class, 'untracedMethod');
        $attributes = $reflection->getAttributes(Traceable::class);

        $this->assertEmpty($attributes, "Method 'untracedMethod' should not have Traceable attribute");
    }
}
