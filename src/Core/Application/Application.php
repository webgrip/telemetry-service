<?php

namespace Webgrip\TelemetryService\Core\Application;

use DI\Container;
use DI\ContainerBuilder;
use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;
use Webgrip\TelemetryService\Core\Application\Factories\LoggerProviderFactoryInterface;
use Webgrip\TelemetryService\Core\Application\Factories\OpenTelemetryCollectorFactoryInterface;
use Webgrip\TelemetryService\Infrastructure\Configuration\Configuration;
use Webgrip\TelemetryService\Infrastructure\Factories\LoggerProviderFactory;
use Webgrip\TelemetryService\Infrastructure\Factories\OpenTelemetryCollectorFactory;
use function DI\create;

final class Application
{
    private Container $container;

    public function __construct(Configuration $configuration)
    {
        $containerBuilder = new ContainerBuilder();

        $containerBuilder->addDefinitions(
            [
                Configuration::class => $configuration,
                ClientInterface::class => create(Client::class),
                OpenTelemetryCollectorFactoryInterface::class => create(OpenTelemetryCollectorFactory::class),
                LoggerProviderFactoryInterface::class => create(LoggerProviderFactory::class),
            ]
        );

        $this->defineDependencies($containerBuilder);
        CommandServiceProvider::register($containerBuilder);

        $this->container = $containerBuilder->build();
    }
}
