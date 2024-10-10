<?php


namespace Webgrip\TelemetryService\Core\Application\Telemetry;

 use OpenTelemetry\API\Trace\TracerProviderInterface;
 use OpenTelemetry\SDK\Logs\LoggerProviderInterface;

 interface OpenTelemetryCollectorInterface
{
    public function createTracerProvider(): TracerProviderInterface;
    public function createLoggerProvider(): LoggerProviderInterface;
}
