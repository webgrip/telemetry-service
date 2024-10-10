<?php

namespace Webgrip\TelemetryService\Core\Application\Factories;

use Webgrip\TelemetryService\Core\Application\Telemetry\OpenTelemetryCollectorInterface;
use Webgrip\TelemetryService\Infrastructure\Configuration\Configuration;

interface OpenTelemetryCollectorFactoryInterface
{
    public function create(Configuration $configuration): OpenTelemetryCollectorInterface;
}
