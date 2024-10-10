<?php

namespace Webgrip\TelemetryService\Core\Application\Factories;

use OpenTelemetry\SDK\Resource\ResourceInfo;
use Webgrip\TelemetryService\Infrastructure\Services\TelemetryService;

interface TelemetryServiceFactoryInterface
{
    public function create(ResourceInfo $resourceInfo): TelemetryService;
}
