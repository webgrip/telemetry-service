<?php

namespace Webgrip\TelemetryService\Infrastructure;

use Psr\Container\ContainerInterface;

class Configuration implements ContainerInterface
{
    public function __construct(
        public string $applicationEnvironmentName,
        public string $applicationNamespace,
        public string $applicationName,
        public string $applicationVersion,
        public string $otelCollectorHost
    ) {
    }

    public function get(string $id)
    {
        return match ($id) {
            'applicationEnvironmentName' => $this->applicationEnvironmentName,
            'applicationNamespace' => $this->applicationNamespace,
            'applicationName' => $this->applicationName,
            'applicationVersion' => $this->applicationVersion,
            'otelCollectorHost' => $this->otelCollectorHost,
            default => null,
        };
    }

    public function has(string $id): bool
    {
        return in_array($id, [
            'applicationEnvironmentName',
            'applicationNamespace',
            'applicationName',
            'applicationVersion',
            'otelCollectorHost',
        ]);
    }
}
