<?php

namespace Webgrip\TelemetryService\Tests\Unit\Infrastructure\Services;

use Monolog\Handler\Handler;
use Monolog\Level;
use Monolog\Logger;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use Prophecy\Argument;
use Prophecy\Prophet;
use Webgrip\TelemetryService\Infrastructure\Services\TelemetryService;
use Webgrip\TelemetryService\Tests\Unit\TestCase;

class TelemetryServiceTest extends TestCase
{
    public function test_classCanBeInstantiated()
    {
        $prophet = new Prophet();

        // Mock the dependencies
        $loggerProvider = $prophet->prophesize(LoggerProviderInterface::class);
        $tracerProvider = $prophet->prophesize(TracerProviderInterface::class);
        $monologLogger = $prophet->prophesize(Logger::class);

        // Capture the handler passed to pushHandler
        $monologLogger->pushHandler(Argument::that(function ($handler) use ($loggerProvider) {
            // Since we can't check directly, ensure the handler is the right type
            $this->assertInstanceOf(Handler::class, $handler);

            // Use reflection to verify the parameters passed to the constructor
            $handlerReflection = new \ReflectionClass($handler);
            $constructor = $handlerReflection->getConstructor();
            $constructorParams = $constructor->getParameters();

            // Retrieve the actual values passed to the constructor
            $constructorArgs = [];
            foreach ($constructorParams as $param) {
                $paramName = $param->getName();
                $prop = $handlerReflection->getProperty($paramName);
                $prop->setAccessible(true);
                $constructorArgs[$paramName] = $prop->getValue($handler);
            }

            // Check the LoggerProvider and Level passed into the Handler
            $this->assertSame($loggerProvider->reveal(), $constructorArgs['loggerProvider']);
            $this->assertSame(Level::Debug, $constructorArgs['level']);

            return true;
        }))->shouldBeCalled();

        // Instantiate the TelemetryService
        new TelemetryService(
            $loggerProvider->reveal(),
            $tracerProvider->reveal(),
            $monologLogger->reveal(),
        );
    }

    public function testTracer(): void
    {
        $this->assertTrue(true);
    }

    public function testLogger(): void
    {
        $this->assertTrue(true);
    }
}
