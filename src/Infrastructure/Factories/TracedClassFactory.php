<?php

namespace Webgrip\TelemetryService\Infrastructure\Factories;

use Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface;
use Webgrip\TelemetryService\Core\Domain\Services\Traceable;
use Webgrip\TelemetryService\Infrastructure\Services\TracingProxy;
use ReflectionClass;

class TracedClassFactory
{
    private TelemetryServiceInterface $telemetryService;

    public function __construct(TelemetryServiceInterface $telemetryService)
    {
        $this->telemetryService = $telemetryService;
    }

    public function create(object $instance): object
    {
        $reflectionClass = new ReflectionClass($instance);
        $hasTraceableAttribute = !empty($reflectionClass->getAttributes(Traceable::class));

        if ($hasTraceableAttribute) {
            return new TracingProxy($instance, $this->telemetryService);
        }

        // If no Traceable attribute, return the instance directly
        return $instance;
    }
}
