<?php

namespace Webgrip\TelemetryService\Core\Domain\Services;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use Psr\Log\LoggerInterface;

interface TelemetryServiceInterface
{
    public function logger(): LoggerInterface;

    public function tracer(): TracerInterface;

    public function registerException(\Throwable $exception, SpanInterface $span): void;

    public function getCurrentSpan(): SpanInterface;
}
