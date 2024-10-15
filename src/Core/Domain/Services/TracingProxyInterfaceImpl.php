<?php

namespace Webgrip\TelemetryService\Core\Domain\Services;

use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use Opentelemetry\Proto\Trace\V1\Status;
use ReflectionMethod;

class TracingProxyInterfaceImpl implements TracingProxyInterface
{
    public function __construct(
        private object $instance,
        private readonly TracerInterface $tracer
    ) {
    }

    public static function create(object $instance, TracerInterface $tracer): object
    {
        return new self($instance, $tracer);
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function __call(string $method, array $arguments): mixed
    {
        $reflectionMethod = new ReflectionMethod($this->instance, $method);
        $attributes = $reflectionMethod->getAttributes(Traceable::class);

        if (!empty($attributes)) {
            $traceableAttribute = $attributes[0]->newInstance();
            $operationName = $traceableAttribute->operationName ?? $method;

            $span = $this->tracer->spanBuilder($operationName)->startSpan();
            $scope = $span->activate();

            try {
                return $reflectionMethod->invokeArgs($this->instance, $arguments);
            } catch (\Throwable $e) {
                $span->recordException($e);
                $span->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());
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
