<?php

namespace Webgrip\TelemetryService\Core\Application\Factories;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Webgrip\TelemetryService\Infrastructure\Services\TelemetryService;

interface TelemetryServiceFactoryInterface
{
    /**
     * @param ContainerInterface $configuration
     * @param LoggerInterface $logger
     * @return TelemetryService
     */
    public function create(ContainerInterface $configuration, LoggerInterface $logger): TelemetryService;
}
