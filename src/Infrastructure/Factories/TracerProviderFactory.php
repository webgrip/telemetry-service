<?php

namespace Webgrip\TelemetryService\Infrastructure\Factories;

use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Webgrip\TelemetryService\Core\Application\Factories\TracerProviderFactoryInterface;

final readonly class TracerProviderFactory implements TracerProviderFactoryInterface
{
    public function __construct(
        private ContainerInterface $configuration
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(ResourceInfo $resourceInfo): TracerProviderInterface
    {
        $otlpHttpTransportFactory = new OtlpHttpTransportFactory();

        $transport = $otlpHttpTransportFactory->create(
            'http://' . $this->configuration->get('otelCollectorHost') . ':4318' . '/v1/traces',
            'application/json'
        );

        $spanExporter = new SpanExporter($transport);

        return new TracerProvider(
            new SimpleSpanProcessor($spanExporter),
            new AlwaysOnSampler(),
            $resourceInfo
        );
    }
}
