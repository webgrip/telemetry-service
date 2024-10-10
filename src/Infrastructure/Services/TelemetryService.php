<?php

namespace Webgrip\TelemetryService\Infrastructure\Services;

use Monolog\Level;
use Monolog\Logger;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;
use Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface;

final class TelemetryService implements TelemetryServiceInterface
{
    public function __construct(
        public LoggerProviderInterface $loggerProvider,
        public TracerProviderInterface $tracerProvider,
        public Logger $logger,
    )
    {
        $this->logger->pushHandler(
            new Handler(
                $loggerProvider,
                Level::Debug
            )
        );
    }

    public function tracer(): TracerInterface
    {
        return $this->tracerProvider->getTracer('io.opentelemetry.contrib.php');
    }
}
