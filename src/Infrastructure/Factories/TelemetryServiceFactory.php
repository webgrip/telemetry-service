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
use OpenTelemetry\SemConv\TraceAttributes;
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

                ResourceAttributes::HOST_IP => $_SERVER['HOST_IP'] ?? null,
                ResourceAttributes::HOST_NAME => $_SERVER['HOSTNAME'] ?? null,

                ResourceAttributes::OS_NAME => php_uname('s') ?? null,
                ResourceAttributes::OS_VERSION => php_uname('v') ?? null,

                ResourceAttributes::PROCESS_COMMAND => $_SERVER['argv'][0] ?? null,
                ResourceAttributes::PROCESS_COMMAND_LINE => implode(' ', $_SERVER['argv']) ?? null,
                ResourceAttributes::PROCESS_OWNER => get_current_user() ?? null,
                ResourceAttributes::PROCESS_PID => getmypid() ?? null,
                ResourceAttributes::PROCESS_RUNTIME_DESCRIPTION => php_uname('m') ?? null,
                ResourceAttributes::PROCESS_RUNTIME_NAME => php_sapi_name() ?? null,
                ResourceAttributes::PROCESS_RUNTIME_VERSION => phpversion() ?? null,

                ResourceAttributes::SERVICE_NAMESPACE => $configuration->get('applicationNamespace'),
                ResourceAttributes::SERVICE_NAME => $configuration->get('applicationName'),
                ResourceAttributes::SERVICE_VERSION => $configuration->get('applicationVersion'),


                TraceAttributes::CLIENT_ADDRESS => $_SERVER['REMOTE_ADDR'] ?? null,
                TraceAttributes::CLIENT_PORT => $_SERVER['REMOTE_PORT'] ?? null,

                TraceAttributes::HTTP_ROUTE => $_SERVER['REQUEST_URI'] ?? null,
                TraceAttributes::HTTP_REQUEST_METHOD => $_SERVER['REQUEST_METHOD'] ?? null,
                TraceAttributes::HTTP_REQUEST_SIZE => $_SERVER['CONTENT_LENGTH'] ?? null,
                TraceAttributes::HTTP_RESPONSE_SIZE => $_SERVER['CONTENT_LENGTH'] ?? null,
                TraceAttributes::HTTP_RESPONSE_STATUS_CODE => $_SERVER['REDIRECT_STATUS'] ?? null,

                TraceAttributes::SERVER_ADDRESS => $_SERVER['SERVER_NAME'] ?? null,
                TraceAttributes::SERVER_PORT => $_SERVER['SERVER_PORT'] ?? null,

                TraceAttributes::SESSION_ID => session_id() ?? null,

                TraceAttributes::URL_SCHEME => $_SERVER['REQUEST_SCHEME'] ?? null,
                TraceAttributes::URL_DOMAIN => $_SERVER['HTTP_HOST'] ?? null,
                TraceAttributes::URL_EXTENSION => $_SERVER['REDIRECT_STATUS'] ?? null,
                TraceAttributes::URL_PATH => $_SERVER['REQUEST_URI'] ?? null,
                TraceAttributes::URL_QUERY => $_SERVER['QUERY_STRING'] ?? null,

                TraceAttributes::USER_AGENT_NAME => $_SERVER['HTTP_USER_AGENT'] ?? null,
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
