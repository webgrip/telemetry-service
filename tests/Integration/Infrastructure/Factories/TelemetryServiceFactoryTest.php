<?php

namespace Webgrip\TelemetryService\Tests\Integration\Infrastructure\Factories;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Webgrip\TelemetryService\Core\Application\Factories\LoggerProviderFactoryInterface;
use Webgrip\TelemetryService\Core\Application\Factories\TracerProviderFactoryInterface;
use Webgrip\TelemetryService\Infrastructure\Factories\TelemetryServiceFactory;
use Webgrip\TelemetryService\Infrastructure\Services\TelemetryService;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;

class TelemetryServiceFactoryTest extends TestCase
{
    /** @var ContainerInterface|MockObject */
    private $configuration;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var ClientInterface|MockObject */
    private $client;

    /** @var LoggerProviderFactoryInterface|MockObject */
    private $loggerProviderFactory;

    /** @var TracerProviderFactoryInterface|MockObject */
    private $tracerProviderFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = $this->createMock(ContainerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->client = $this->createMock(ClientInterface::class);
        $this->loggerProviderFactory = $this->createMock(LoggerProviderFactoryInterface::class);
        $this->tracerProviderFactory = $this->createMock(TracerProviderFactoryInterface::class);
    }

    public function testCreateWithoutOtelCollectorHost(): void
    {
        $this->configuration->method('get')
            ->willReturnMap([
                ['otelCollectorHost', null],
                ['applicationEnvironmentName', 'test-env'],
                ['applicationNamespace', 'test-namespace'],
                ['applicationName', 'test-app'],
                ['applicationVersion', '1.0.0'],
            ]);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Telemetry collector host (otelCollectorHost) is not configured.');

        $factory = new TelemetryServiceFactory(
            $this->loggerProviderFactory,
            $this->tracerProviderFactory,
            $this->client
        );

        $telemetryService = $factory->create($this->configuration, $this->logger);

        $this->assertInstanceOf(TelemetryService::class, $telemetryService);
        $this->assertInstanceOf(NoopLoggerProvider::class, $telemetryService->getLoggerProvider());
        $this->assertInstanceOf(NoopTracerProvider::class, $telemetryService->getTracerProvider());
    }

    public function testCreateWithHealthCheckFailure(): void
    {
        $otelCollectorHost = 'localhost';
        $this->configuration->method('get')
            ->willReturnMap([
                ['otelCollectorHost', $otelCollectorHost],
                ['applicationEnvironmentName', 'test-env'],
                ['applicationNamespace', 'test-namespace'],
                ['applicationName', 'test-app'],
                ['applicationVersion', '1.0.0'],
            ]);

        $this->client->method('get')
            ->willThrowException(new ConnectException('Connection refused', new Request('GET', '')));

        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('Health check for telemetry collector to http://' . $otelCollectorHost . ':13133 failed with exception'),
                $this->arrayHasKey('exception')
            );

        $factory = new TelemetryServiceFactory(
            $this->loggerProviderFactory,
            $this->tracerProviderFactory,
            $this->client
        );

        $telemetryService = $factory->create($this->configuration, $this->logger);

        $this->assertInstanceOf(TelemetryService::class, $telemetryService);
        $this->assertInstanceOf(NoopLoggerProvider::class, $telemetryService->getLoggerProvider());
        $this->assertInstanceOf(NoopTracerProvider::class, $telemetryService->getTracerProvider());
    }

    public function testCreateWithHealthCheckSuccess(): void
    {
        $otelCollectorHost = 'localhost';
        $this->configuration->method('get')
            ->willReturnMap([
                ['otelCollectorHost', $otelCollectorHost],
                ['applicationEnvironmentName', 'test-env'],
                ['applicationNamespace', 'test-namespace'],
                ['applicationName', 'test-app'],
                ['applicationVersion', '1.0.0'],
            ]);

        $this->client->method('get')
            ->willReturn(new Response(200));

        $loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $tracerProvider = $this->createMock(TracerProviderInterface::class);

        $this->loggerProviderFactory->method('create')
            ->willReturn($loggerProvider);

        $this->tracerProviderFactory->method('create')
            ->willReturn($tracerProvider);

        $factory = new TelemetryServiceFactory(
            $this->loggerProviderFactory,
            $this->tracerProviderFactory,
            $this->client
        );

        $telemetryService = $factory->create($this->configuration, $this->logger);

        $this->assertInstanceOf(TelemetryService::class, $telemetryService);
        $this->assertSame($loggerProvider, $telemetryService->getLoggerProvider());
        $this->assertSame($tracerProvider, $telemetryService->getTracerProvider());
    }
}
