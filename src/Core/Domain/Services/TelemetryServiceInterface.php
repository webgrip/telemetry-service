<?php

namespace Webgrip\TelemetryService\Core\Domain\Services;

use OpenTelemetry\API\Trace\TracerInterface;

interface TelemetryServiceInterface
{
    public function tracer(): TracerInterface;
}
