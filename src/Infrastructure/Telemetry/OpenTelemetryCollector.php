<?php

namespace Webgrip\TelemetryService\Infrastructure\Telemetry;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SemConv\ResourceAttributes;
use Vendic\Vencore\Core\Application\Config\ConfigurationAbstract;
use Vendic\Vencore\Core\Application\Telemetry\OpenTelemetryCollectorInterface;
use Webgrip\TelemetryService\Infrastructure\Configuration\Configuration;

final class OpenTelemetryCollector implements OpenTelemetryCollectorInterface
{
    public ResourceInfo $resource;

    private static string $healthCheckProtocol = 'http://';
    private static string $healthCheckPort = ':13133';

    public function __construct(
        private readonly Configuration $configuration
    ) {
        $this->resource = ResourceInfoFactory::emptyResource()->merge(
            ResourceInfo::create(
                Attributes::create([
                    ResourceAttributes::DEPLOYMENT_ENVIRONMENT_NAME => $this->configuration->applicationEnvironmentName,
                    ResourceAttributes::SERVICE_NAMESPACE => $this->configuration->applicationNamespace,
                    ResourceAttributes::SERVICE_NAME => $this->configuration->applicationName,
                    ResourceAttributes::SERVICE_VERSION => $this->configuration->applicationVersion,
                ])
            )
        );
    }

    public function getHealthCheckPath(): string
    {
        return self::$healthCheckProtocol . $this->configuration->otelCollectorHost . self::$healthCheckPort;
    }
}
