<?php

namespace Webgrip\TelemetryService\Core\Domain\Services;


interface TracingProxyInterface
{
    public function __construct(object $instance, TelemetryServiceInterface $tracer);

    /**
     * @param array<mixed, mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed;
}
