<?php

namespace Webgrip\TelemetryService\Core\Application;

use DI\Container;
use GuzzleHttp\Client;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Webgrip\TelemetryService\Core\Application\Factories\LoggerProviderFactoryInterface;
use Webgrip\TelemetryService\Core\Application\Factories\TelemetryServiceFactoryInterface;
use Webgrip\TelemetryService\Core\Application\Factories\TracerProviderFactoryInterface;
use Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface;
use Webgrip\TelemetryService\Infrastructure\Factories\LoggerProviderFactory;
use Webgrip\TelemetryService\Infrastructure\Factories\TelemetryServiceFactory;
use Webgrip\TelemetryService\Infrastructure\Factories\TracerProviderFactory;

readonly class TelemetryServiceProvider
{
    /**
     * @param ContainerInterface $configuration
     */
    public function __construct(
        private ContainerInterface $configuration
    ) {
    }

    /**
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        $configuration = $this->configuration;

        $container->set(ResourceInfo::class, function (Container $container) use ($configuration) {
            $attributes = Attributes::create([
                ResourceAttributes::DEPLOYMENT_ENVIRONMENT_NAME => $configuration->get('applicationEnvironmentName'),
                ResourceAttributes::SERVICE_NAMESPACE => $configuration->get('applicationNamespace'),
                ResourceAttributes::SERVICE_NAME => $configuration->get('applicationName'),
                ResourceAttributes::SERVICE_VERSION => $configuration->get('applicationVersion'),
            ]);

            return ResourceInfo::create($attributes);
        });

        $container->set(LoggerProviderFactoryInterface::class, function (Container $container) use ($configuration) {
            return new LoggerProviderFactory(
                $configuration
            );
        });

        $container->set(TracerProviderFactoryInterface::class, function (Container $container) use ($configuration) {
            return new TracerProviderFactory(
                $configuration
            );
        });

        $container->set(TelemetryServiceFactoryInterface::class, function (Container $container) {
            return new TelemetryServiceFactory(
                $container->get(LoggerProviderFactoryInterface::class),
                $container->get(TracerProviderFactoryInterface::class),
                $container->get(Client::class)
            );
        });

        $container->set(TelemetryServiceInterface::class, function (Container $container) use ($configuration) {
            return $container->get(TelemetryServiceFactoryInterface::class)->create(
                $configuration,
                $container->get(LoggerInterface::class)
            );
        });
    }
}
