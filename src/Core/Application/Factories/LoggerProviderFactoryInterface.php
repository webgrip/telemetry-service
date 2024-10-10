<?php

namespace Webgrip\TelemetryService\Core\Application\Factories;

use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

interface LoggerProviderFactoryInterface
{
    public function create(ResourceInfo $resourceInfo): LoggerProviderInterface;
}
