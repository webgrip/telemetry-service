<?php

namespace Webgrip\TelemetryService\Core\Application\Factories;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Webgrip\TelemetryService\Infrastructure\Services\TelemetryService;

interface TelemetryServiceFactoryInterface
{
    /**
     * @param ContainerInterface $configuration
     * @param Logger $logger
     * @return TelemetryService
     */
    public function create(ContainerInterface $configuration, Logger $logger): TelemetryService;
}
