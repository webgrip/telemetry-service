<?php

namespace Webgrip\TelemetryService\Infrastructure\Factories;

use ReflectionClass;
use Webgrip\TelemetryService\Core\Domain\Attributes\Traceable;
use Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface;
use Webgrip\TelemetryService\Infrastructure\Services\TracingProxy;

class TracedClassFactory
{
    public function __construct(private TelemetryServiceInterface $telemetryService)
    {
    }

    public function create(object $instance): object
    {
        $reflectionClass = new ReflectionClass($instance);
        $hasTraceableAttribute = $reflectionClass->getAttributes(Traceable::class) !== [];

        if ($hasTraceableAttribute) {
            return new TracingProxy($instance, $this->telemetryService);
        }

        // If no Traceable attribute, return the instance directly
        return $instance;
    }
}
