<?php

namespace Webgrip\TelemetryService\Core\Domain\Services;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use Psr\Log\LoggerInterface;

interface TelemetryServiceInterface
{
    /**
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface;

    /**
     * @return TracerInterface
     */
    public function tracer(): TracerInterface;

    /**
     * @param \Throwable $exception
     * @param SpanInterface $span
     * @return void
     */
    public function registerException(\Throwable $exception, SpanInterface $span): void;

    /**
     * @return SpanInterface
     */
    public function getCurrentSpan(): SpanInterface;
}
