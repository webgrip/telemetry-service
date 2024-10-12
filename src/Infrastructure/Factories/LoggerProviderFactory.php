<?php

namespace Webgrip\TelemetryService\Infrastructure\Factories;

use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Webgrip\TelemetryService\Core\Application\Factories\LoggerProviderFactoryInterface;

final class LoggerProviderFactory implements LoggerProviderFactoryInterface
{
    /**
     * @param ContainerInterface $configuration
     */
    public function __construct(
        private readonly ContainerInterface $configuration
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(ResourceInfo $resourceInfo): LoggerProviderInterface
    {
        $transport = (new OtlpHttpTransportFactory())->create(
            'http://' . $this->configuration->get('otelCollectorHost') . ':4318' . '/v1/logs',
            'application/json'
        );

        $logExporter = new LogsExporter($transport);

        return LoggerProvider::builder()
            ->setResource($resourceInfo)
            ->addLogRecordProcessor(new SimpleLogRecordProcessor($logExporter))
            ->build();
    }
}
