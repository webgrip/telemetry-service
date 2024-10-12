<?php

namespace Webgrip\TelemetryService\Tests\Unit\Infrastructure\Services;

use Monolog\Logger;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use Prophecy\Argument;
use Webgrip\TelemetryService\Infrastructure\Services\TelemetryService;
use Webgrip\TelemetryService\Tests\Unit\TestCase;

class TelemetryServiceTest extends TestCase
{
    private $loggerProvider;
    private $tracerProvider;
    private $logger;
    private $telemetryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loggerProvider = $this->prophesize(LoggerProviderInterface::class);
        $this->tracerProvider = $this->prophesize(TracerProviderInterface::class);
        $this->logger = $this->prophesize(Logger::class);

        $logger = $this->logger->reveal();

        // Return the logger itself for pushHandler() to avoid TypeError
        $this->logger->pushHandler(Argument::any())->willReturn($logger);

        $this->telemetryService = new TelemetryService(
            $this->loggerProvider->reveal(),
            $this->tracerProvider->reveal(),
            $logger
        );
    }

    public function testLoggerReturnsInjectedLoggerInstance(): void
    {
        $result = $this->telemetryService->logger();

        // Assert that the returned logger is the one passed to the constructor
        $this->assertSame($result, $this->logger->reveal());
    }

    public function testTracerReturnsTracerFromProvider(): void
    {
        // Create a mock for TracerInterface and set expectations
        $tracer = $this->prophesize(TracerInterface::class);
        $this->tracerProvider
            ->getTracer('io.opentelemetry.contrib.php')
            ->willReturn($tracer->reveal())
            ->shouldBeCalled();

        $result = $this->telemetryService->tracer();

        // Assert that the returned tracer matches the tracer from the provider
        $this->assertSame($tracer->reveal(), $result);

        $this->prophet->checkPredictions();
    }
}
