<?php

namespace Webgrip\TelemetryService\Infrastructure\Factories;

use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Webgrip\TelemetryService\Core\Application\Factories\TracerProviderFactoryInterface;
use Webgrip\TelemetryService\Infrastructure\Configuration\Configuration;

final class TracerProviderFactory implements TracerProviderFactoryInterface
{
    public function __construct(
        private readonly Configuration $configuration
    )
    {
    }

    public function create(ResourceInfo $resourceInfo): TracerProviderInterface
    {
        $otlpHttpTransportFactory = new OtlpHttpTransportFactory();

        $transport = $otlpHttpTransportFactory->create(
            'http://' . $this->configuration->otelCollectorHost . ':4317' . '/v1/traces',
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
