<?php

namespace Webgrip\TelemetryService\Infrastructure\Factories;

use OpenTelemetry\SDK\Resource\ResourceInfo;
use Webgrip\TelemetryService\Core\Application\Factories\LoggerProviderFactoryInterface;
use Webgrip\TelemetryService\Core\Application\Factories\TelemetryServiceFactoryInterface;
use Webgrip\TelemetryService\Core\Application\Factories\TracerProviderFactoryInterface;
use Webgrip\TelemetryService\Infrastructure\Services\TelemetryService;

final class TelemetryServiceFactory implements TelemetryServiceFactoryInterface
{
    public function __construct(
        private LoggerProviderFactoryInterface $loggerProviderFactory,
        private TracerProviderFactoryInterface $tracerProviderFactory
    )
    {
    }

    public function create(ResourceInfo $resourceInfo): TelemetryService
    {
        return new TelemetryService(
            $this->loggerProviderFactory->create($resourceInfo),
            $this->tracerProviderFactory->create($resourceInfo)
        );
    }
}
