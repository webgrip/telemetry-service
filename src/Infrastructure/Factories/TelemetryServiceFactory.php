<?php

namespace Webgrip\TelemetryService\Infrastructure\Factories;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\CancellationException;
use Monolog\Logger;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Webgrip\TelemetryService\Core\Application\Factories\LoggerProviderFactoryInterface;
use Webgrip\TelemetryService\Core\Application\Factories\TelemetryServiceFactoryInterface;
use Webgrip\TelemetryService\Core\Application\Factories\TracerProviderFactoryInterface;
use Webgrip\TelemetryService\Infrastructure\Services\TelemetryService;
use Webgrip\TelemetryService\Infrastructure\Telemetry\NoopOpenTelemetryCollector;

final class TelemetryServiceFactory implements TelemetryServiceFactoryInterface
{
    /**
     * @param LoggerProviderFactoryInterface $loggerProviderFactory
     * @param TracerProviderFactoryInterface $tracerProviderFactory
     * @param ClientInterface $client
     */
    public function __construct(
        private LoggerProviderFactoryInterface $loggerProviderFactory,
        private TracerProviderFactoryInterface $tracerProviderFactory,
        private ClientInterface $client,
    ) {
    }

    /**
     * @param ContainerInterface $configuration
     * @param Logger $logger
     * @param $throwExceptionWhenHealthCheckFails
     * @return TelemetryService
     * @throws ContainerExceptionInterface
     * @throws GuzzleException
     * @throws NotFoundExceptionInterface
     */
    public function create(
        ContainerInterface $configuration,
        Logger $logger,
        $throwExceptionWhenHealthCheckFails = true
    ): TelemetryService {
        $resourceInfo = ResourceInfo::create(
            Attributes::create([
                ResourceAttributes::DEPLOYMENT_ENVIRONMENT_NAME => $configuration->get('applicationEnvironmentName'),
                ResourceAttributes::SERVICE_NAMESPACE => $configuration->get('applicationNamespace'),
                ResourceAttributes::SERVICE_NAME => $configuration->get('applicationName'),
                ResourceAttributes::SERVICE_VERSION => $configuration->get('applicationVersion'),
            ])
        );

        try {
            $response = $this->client->get('http://' . $configuration->get('otelCollectorHost') . ':13133');
            $statusCode = $response->getStatusCode();

            if (!in_array($statusCode, [Response::HTTP_OK, Response::HTTP_NO_CONTENT])) {
                throw new CancellationException('Health check failed. Status code: ' . $statusCode);
            }
        } catch (GuzzleException | CancellationException $e) {
            if ($throwExceptionWhenHealthCheckFails) {
                throw $e;
            }

            return new TelemetryService(
                new NoopLoggerProvider(),
                new NoopTracerProvider(),
                $logger
            );
        }

        return new TelemetryService(
            $this->loggerProviderFactory->create($resourceInfo),
            $this->tracerProviderFactory->create($resourceInfo),
            $logger
        );
    }
}
