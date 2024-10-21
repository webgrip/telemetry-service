<?php

namespace Webgrip\TelemetryService\Infrastructure\Factories;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\CancellationException;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Webgrip\TelemetryService\Core\Application\Factories\LoggerProviderFactoryInterface;
use Webgrip\TelemetryService\Core\Application\Factories\TelemetryServiceFactoryInterface;
use Webgrip\TelemetryService\Core\Application\Factories\TracerProviderFactoryInterface;
use Webgrip\TelemetryService\Infrastructure\Services\TelemetryService;

final class TelemetryServiceFactory implements TelemetryServiceFactoryInterface
{
    public function __construct(
        private LoggerProviderFactoryInterface $loggerProviderFactory,
        private TracerProviderFactoryInterface $tracerProviderFactory,
        private ClientInterface $client,
    ) {
    }

    /**
     * @throws GuzzleException
     */
    public function create(
        ContainerInterface $configuration,
        LoggerInterface $logger
    ): TelemetryService {
        $resourceInfo = $this->createResourceInfo($configuration);

        $otelCollectorHost = $configuration->get('otelCollectorHost');

        if (
            empty($otelCollectorHost) ||
            !$this->collectorIsHealthy($otelCollectorHost, $logger)
        ) {
            $logger->error('Telemetry collector host (otelCollectorHost) is not configured.');
            return $this->createNoopTelemetryService($logger);
        }

        return new TelemetryService(
            $this->loggerProviderFactory->create($resourceInfo),
            $this->tracerProviderFactory->create($resourceInfo),
            $logger
        );
    }

    private function createResourceInfo(ContainerInterface $configuration): ResourceInfo
    {
        return ResourceInfo::create(
            Attributes::create([
                ResourceAttributes::DEPLOYMENT_ENVIRONMENT_NAME => $configuration->get('applicationEnvironmentName'),

                ResourceAttributes::HOST_IP => $_SERVER['HOST_IP'] ?? null,
                ResourceAttributes::HOST_NAME => $_SERVER['HOSTNAME'] ?? null,

                ResourceAttributes::OS_NAME => php_uname('s') ?? null,
                ResourceAttributes::OS_VERSION => php_uname('v') ?? null,

                ResourceAttributes::PROCESS_COMMAND => $_SERVER['argv'][0] ?? null,
                ResourceAttributes::PROCESS_COMMAND_LINE => isset($_SERVER['argv']) ? implode(' ', $_SERVER['argv']) : null,
                ResourceAttributes::PROCESS_OWNER => get_current_user() ?? null,
                ResourceAttributes::PROCESS_PID => getmypid() ?? null,
                ResourceAttributes::PROCESS_RUNTIME_DESCRIPTION => php_uname('m') ?? null,
                ResourceAttributes::PROCESS_RUNTIME_NAME => PHP_SAPI ?? null,
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

                TraceAttributes::SESSION_ID => session_id() ?: null,

                TraceAttributes::URL_SCHEME => $_SERVER['REQUEST_SCHEME'] ?? null,
                TraceAttributes::URL_DOMAIN => $_SERVER['HTTP_HOST'] ?? null,
                TraceAttributes::URL_EXTENSION => $_SERVER['REDIRECT_STATUS'] ?? null,
                TraceAttributes::URL_PATH => $_SERVER['REQUEST_URI'] ?? null,
                TraceAttributes::URL_QUERY => $_SERVER['QUERY_STRING'] ?? null,

                TraceAttributes::USER_AGENT_NAME => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ])
        );
    }

    private function collectorIsHealthy(string $otelCollectorHost, LoggerInterface $logger): bool
    {
        $healthCheckUrl = 'http://' . $otelCollectorHost . ':13133';

        try {
            $response = $this->client->get($healthCheckUrl);
            $statusCode = $response->getStatusCode();

            if (!in_array($statusCode, [Response::HTTP_OK, Response::HTTP_NO_CONTENT], true)) {
                $logger->warning('Health check failed. Status code: ' . $statusCode);
                return false;
            }
        } catch (GuzzleException | CancellationException $e) {
            $logger->warning(
                'Health check for telemetry collector to ' . $healthCheckUrl . ' failed with exception: ' . $e->getMessage(),
                ['exception' => $e]
            );
            return false;
        }

        return true;
    }

    private function createNoopTelemetryService(LoggerInterface $logger): TelemetryService
    {
        return new TelemetryService(
            new NoopLoggerProvider(),
            new NoopTracerProvider(),
            $logger
        );
    }
}
