<?php

namespace Webgrip\TelemetryService\Core\Domain\Services;

use OpenTelemetry\API\Trace\TracerInterface;

interface TracingProxyInterface
{
    public function __construct(object $instance, TracerInterface $tracer);
    public function __call(string $method, array $arguments): mixed;
}
