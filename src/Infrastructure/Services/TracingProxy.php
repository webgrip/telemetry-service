<?php

namespace Webgrip\TelemetryService\Infrastructure\Services;

use OpenTelemetry\API\Trace\SpanInterface;
use ReflectionClass;
use ReflectionMethod;
use Webgrip\TelemetryService\Core\Domain\Attributes\Traceable;
use Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface;
use Webgrip\TelemetryService\Core\Domain\Services\TracingProxyInterface;

class TracingProxy implements TracingProxyInterface
{

    public SpanInterface $span;


    private bool $traceAllMethods;

    public function __construct(private object $instance, public TelemetryServiceInterface $telemetryService)
    {
        // Check if the class itself is marked as Traceable
        $reflectionClass = new ReflectionClass($this->instance);
        $this->traceAllMethods = $reflectionClass->getAttributes(Traceable::class) !== [];
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function __call(string $method, array $arguments): mixed
    {
        $reflectionMethod = new ReflectionMethod($this->instance, $method);
        $traceableAttributes = $reflectionMethod->getAttributes(Traceable::class);
        $traceableMethod = $traceableAttributes !== [];

        // Set operation name from attribute or use the method name as fallback
        $operationName = $method;
        if ($traceableMethod) {
            /** @var Traceable $traceableInstance */
            $traceableInstance = $traceableAttributes[0]->newInstance();
            $operationName = $traceableInstance->operationName ?? $method;
        }

        if ($this->traceAllMethods || $traceableMethod) {
            $this->span = $this->telemetryService->tracer()
                ->spanBuilder($operationName)
                ->startSpan();

            $scope = $this->span->activate();

            try {
                // Invoke the original method with the arguments
                return $reflectionMethod->invokeArgs($this->instance, $arguments);
            } catch (\Throwable $e) {
                $this->telemetryService->registerException($e, $this->span);
                throw $e;
            } finally {
                $scope->detach();
                $this->span->end();
            }
        } else {
            return $reflectionMethod->invokeArgs($this->instance, $arguments);
        }
    }
}
