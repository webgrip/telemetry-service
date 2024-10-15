<?php

namespace Webgrip\TelemetryService\Infrastructure\Services;

use ReflectionClass;
use ReflectionMethod;
use Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface;
use Webgrip\TelemetryService\Core\Domain\Services\Traceable;

class TracingProxy
{
    private object $instance;
    private TelemetryServiceInterface $telemetryService;

    /**
     * @param object $instance
     * @param TelemetryServiceInterface $telemetryService
     */
    public function __construct(object $instance, TelemetryServiceInterface $telemetryService)
    {
        $this->instance = $instance;
        $this->telemetryService = $telemetryService;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function __call(string $method, array $arguments)
    {
        $reflectionClass = new ReflectionClass($this->instance);
        $reflectionMethod = new ReflectionMethod($this->instance, $method);

        // Check if the class or method has the Traceable attribute
        $traceableClass = !empty($reflectionClass->getAttributes(Traceable::class));
        $traceableMethod = !empty($reflectionMethod->getAttributes(Traceable::class));

        if ($traceableClass || $traceableMethod) {
            // Determine the operation name based on the attribute or the method name
            $operationName = $traceableClass
                ? $reflectionClass->getShortName()
                : $method;

            // Start a span
            $span = $this->telemetryService->tracer()
                ->spanBuilder($operationName)
                ->startSpan();
            $scope = $span->activate();

            try {
                // Call the original method
                return $reflectionMethod->invokeArgs($this->instance, $arguments);
            } catch (\Throwable $e) {
                // Record the exception and set status to error
                $this->telemetryService->registerException($e, $span);
                throw $e;
            } finally {
                // End the span and detach the scope
                $scope->detach();
                $span->end();
            }
        } else {
            // If no Traceable attribute, invoke the method as usual
            return $reflectionMethod->invokeArgs($this->instance, $arguments);
        }
    }
}
