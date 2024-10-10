<?php

namespace Webgrip\TelemetryService\Core\Application\Factories;

use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\API\Trace\TracerProviderInterface;

interface TracerProviderFactoryInterface
{
    public function create(ResourceInfo $resourceInfo): TracerProviderInterface;
}
