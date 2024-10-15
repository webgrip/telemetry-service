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
    private bool $traceAllMethods;

    /**
     * @param object $instance
     * @param TelemetryServiceInterface $telemetryService
     */
    public function __construct(object $instance, TelemetryServiceInterface $telemetryService)
    {
        $this->instance = $instance;
        $this->telemetryService = $telemetryService;

        // Check if the class itself is marked as Traceable
        $reflectionClass = new ReflectionClass($this->instance);
        $this->traceAllMethods = !empty($reflectionClass->getAttributes(Traceable::class));
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
        $reflectionMethod = new ReflectionMethod($this->instance, $method);
        $traceableMethod = !empty($reflectionMethod->getAttributes(Traceable::class));

        if ($this->traceAllMethods || $traceableMethod) {
            $operationName = $traceableMethod ?
                $method :
                (new ReflectionClass($this->instance))->getName();

            $span = $this->telemetryService->tracer()
                ->spanBuilder($operationName)
                ->startSpan();
            $scope = $span->activate();

            try {
                return $reflectionMethod->invokeArgs($this->instance, $arguments);
            } catch (\Throwable $e) {
                $this->telemetryService->registerException($e, $span);
                throw $e;
            } finally {
                $scope->detach();
                $span->end();
            }
        } else {
            return $reflectionMethod->invokeArgs($this->instance, $arguments);
        }
    }
}
