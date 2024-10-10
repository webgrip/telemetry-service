<?php

$configuration = new \Webgrip\TelemetryService\Infrastructure\Configuration\Configuration(
    applicationEnvironmentName: 'development',
    applicationNamespace: 'Webgrip',
    applicationName: 'Webgrip',
    applicationVersion: '1.0.0',
    otelCollectorHost: 'localhost:4317',
);

/**
 * @var \Psr\Log\LoggerInterface $logger
 */
$logger;

$client = new \GuzzleHttp\Client();

$openTelemetryCollectorFactory = new \Webgrip\TelemetryService\Infrastructure\Factories\OpenTelemetryCollectorFactory($client);
$openTelemetryCollector = $openTelemetryCollectorFactory->create($configuration);

$tracerProviderFactory = new \Webgrip\TelemetryService\Infrastructure\Factories\TracerProviderFactory($configuration);
$tracerProvider = $tracerProviderFactory->create($openTelemetryCollector->resource);

$loggerProviderFactory = new \Webgrip\TelemetryService\Infrastructure\Factories\LoggerProviderFactory($configuration);
$loggerProvider = $loggerProviderFactory->create($openTelemetryCollector->resource);

$telemetryServiceFactory = new \Webgrip\TelemetryService\Infrastructure\Factories\TelemetryServiceFactory();
$telemetryService = $telemetryServiceFactory->create($openTelemetryCollector->resource);

$telemetryService->x();



$openTelemetryCollectorFactory->create


