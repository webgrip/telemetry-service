<?php

namespace Webgrip\TelemetryService\Tests\Integration\Core\Application;

use DI\Container;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Webgrip\TelemetryService\Core\Application\TelemetryServiceProvider;
use Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface;
use Webgrip\TelemetryService\Infrastructure\Configuration;

class TelemetryServiceProviderTest extends \Webgrip\TelemetryService\Tests\Unit\TestCase
{
    public function testRegister(): void
    {
        $container = new Container();

        $container->set(
            LoggerInterface::class,
            new NullLogger()
        );

        $configuration = new Configuration(
            'production',
            'com.example',
            'TestApp',
            '1.0.0',
            'localhost',
        );

        $telemetryServiceProvider = new TelemetryServiceProvider($configuration);
        $telemetryServiceProvider->register($container);

        $this->assertInstanceOf(
            TelemetryServiceInterface::class,
            $container->get(TelemetryServiceInterface::class)
        );
    }
}
