<?php

namespace Webgrip\TelemetryService\Infrastructure\Telemetry;

use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\NoopLoggerProvider;
use Vendic\Vencore\Core\Application\Config\ConfigurationAbstract;
use Vendic\Vencore\Core\Application\Telemetry\OpenTelemetryCollectorInterface;
use Webgrip\TelemetryService\Infrastructure\Configuration\Configuration;

final class NoopOpenTelemetryCollector implements OpenTelemetryCollectorInterface
{
    public function __construct(
        private readonly Configuration $configuration
    ) {
    }

    public function createTracerProvider(): TracerProviderInterface
    {
        return new NoopTracerProvider();
    }

    public function createLoggerProvider(): LoggerProviderInterface
    {
        return new NoopLoggerProvider();
    }
}
