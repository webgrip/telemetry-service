<?php

namespace Webgrip\TelemetryService\Infrastructure\Services;

use Monolog\Level;
use Monolog\Logger;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Contrib\Logs\Monolog\Handler;
use Psr\Log\LoggerInterface;
use Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface;

final readonly class TelemetryService implements TelemetryServiceInterface
{
    /**
     * @param LoggerProviderInterface $loggerProvider
     * @param TracerProviderInterface $tracerProvider
     * @param Logger $logger
     */
    public function __construct(
        private LoggerProviderInterface $loggerProvider,
        private TracerProviderInterface $tracerProvider,
        private Logger $logger,
    ) {
        $this->logger->pushHandler(
            new Handler(
                $this->loggerProvider,
                Level::Debug
            )
        );
    }

    /**
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return TracerInterface
     */
    public function tracer(): TracerInterface
    {
        return $this->tracerProvider->getTracer('io.opentelemetry.contrib.php');
    }

    public function registerException(\Throwable $exception, SpanInterface $span): void
    {
        $this->logger()->error($exception->getMessage(), ['exception' => $exception]);
        $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
        $span->recordException($exception);
    }
}
