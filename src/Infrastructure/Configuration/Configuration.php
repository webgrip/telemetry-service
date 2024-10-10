<?php

namespace Webgrip\TelemetryService\Infrastructure\Configuration;

use Psr\Container\ContainerInterface;

final class Configuration implements ContainerInterface
{
    public function __construct(
        public string $applicationEnvironmentName,
        public string $applicationNamespace,
        public string $applicationName,
        public string $applicationVersion,
        public string $otelCollectorHost,
    )
    {
    }

    public function get(string $id)
    {
        // TODO: Implement get() method.
    }

    public function has(string $id): bool
    {
        // TODO: Implement has() method.
    }
}
