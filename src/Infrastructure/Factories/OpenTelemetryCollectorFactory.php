<?php

namespace Webgrip\TelemetryService\Infrastructure\Factories;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\CancellationException;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Webgrip\TelemetryService\Core\Application\Factories\OpenTelemetryCollectorFactoryInterface;
use Webgrip\TelemetryService\Infrastructure\Configuration\Configuration;
use Webgrip\TelemetryService\Infrastructure\Telemetry\NoopOpenTelemetryCollector;
use Webgrip\TelemetryService\Infrastructure\Telemetry\OpenTelemetryCollector;

final class OpenTelemetryCollectorFactory implements OpenTelemetryCollectorFactoryInterface
{
    public function __construct(
        private readonly ClientInterface $client
    ) {
    }

    /**
     * @throws GuzzleException
     */
    public function create(Configuration $configuration, $throwExceptionWhenHealthCheckFails = true): OpenTelemetryCollector|NoopOpenTelemetryCollector
    {
        $collector = new OpenTelemetryCollector($configuration);

        try {
            $response = $this->client->get($collector->getHealthCheckPath());
            $statusCode = $response->getStatusCode();

            if (Response::HTTP_NO_CONTENT !== $statusCode) {
                throw new CancellationException('Health check failed. Status code: ' . $statusCode);
            }
        } catch (GuzzleException | CancellationException $e) {
            if ($throwExceptionWhenHealthCheckFails) {
                throw $e;
            }

            $collector = new NoopOpenTelemetryCollector($configuration);
        }

        return $collector;
    }
}
