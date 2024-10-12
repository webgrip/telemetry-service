<?php

namespace Webgrip\TelemetryService\Tests\Integration\Core\Application;

class TelemetryServiceProviderTest extends \Webgrip\TelemetryService\Tests\Unit\TestCase
{
    public function testRegister(): void
    {

        $container = new \DI\Container();

        $container->set('config', function() {
            return [
                'applicationEnvironmentName' => 'production',
                'applicationNamespace' => 'com.example',
                'applicationName' => 'TestApp',
                'applicationVersion' => '1.0.0',
                'otelCollectorHost' => 'localhost',
            ];
        });

        $configuration = $container->get('config');

        $telemetryServiceProvider = new \Webgrip\TelemetryService\Core\Application\TelemetryServiceProvider($configuration);

        $telemetryServiceProvider->register($container);

        $telemetryService = $container->get(\Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface::class);

        $telemetryService->
    }
}
