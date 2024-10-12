<?php

namespace Webgrip\TelemetryService\Core\Domain\Services;

use OpenTelemetry\API\Trace\TracerInterface;
use Psr\Log\LoggerInterface;

interface TelemetryServiceInterface
{
    public function logger(): LoggerInterface;
    public function tracer(): TracerInterface;
}
