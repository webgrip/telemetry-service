<?php

namespace Webgrip\TelemetryService\Tests\Unit\Core\Application;

use DI\Container;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Webgrip\TelemetryService\Core\Application\Factories\LoggerProviderFactoryInterface;
use Webgrip\TelemetryService\Core\Application\Factories\TelemetryServiceFactoryInterface;
use Webgrip\TelemetryService\Core\Application\Factories\TracerProviderFactoryInterface;
use Webgrip\TelemetryService\Core\Application\TelemetryServiceProvider;
use Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface;
use GuzzleHttp\Client;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use Psr\Log\LoggerInterface;
use Webgrip\TelemetryService\Tests\Unit\TestCase;

class TelemetryServiceProviderTest extends TestCase
{
    private \Prophecy\Prophecy\ObjectProphecy|ContainerInterface $configuration;
    private \Prophecy\Prophecy\ObjectProphecy|Container $container;
    private TelemetryServiceProvider $telemetryServiceProvider;

    protected function setUp(): void
    {
        parent::setUp();

        // Prophecy for configuration and container
        $this->configuration = $this->prophesize(ContainerInterface::class);
        $this->container = $this->prophesize(Container::class);

        // Instantiate the class being tested
        $this->telemetryServiceProvider = new TelemetryServiceProvider($this->configuration->reveal());
    }

    public function testRegisterAllServices(): void
    {
        // Set up expectations for configuration values
        $this->configuration->get('applicationEnvironmentName')->willReturn('production');
        $this->configuration->get('applicationNamespace')->willReturn('com.example');
        $this->configuration->get('applicationName')->willReturn('TestApp');
        $this->configuration->get('applicationVersion')->willReturn('1.0.0');

        // Mock dependencies that are retrieved from the container
        $logger = $this->prophesize(LoggerInterface::class);
        $loggerProviderFactory = $this->prophesize(LoggerProviderFactoryInterface::class);
        $tracerProviderFactory = $this->prophesize(TracerProviderFactoryInterface::class);
        $client = $this->prophesize(Client::class);
        $telemetryServiceFactory = $this->prophesize(TelemetryServiceFactoryInterface::class);
        $telemetryService = $this->prophesize(TelemetryServiceInterface::class);

        // Set up container expectations
        $this->container->set(ResourceInfo::class, Argument::any())->shouldBeCalled();
        $this->container->set(LoggerProviderFactoryInterface::class, Argument::type('callable'))->shouldBeCalled();
        $this->container->set(TracerProviderFactoryInterface::class, Argument::type('callable'))->shouldBeCalled();
        $this->container->set(TelemetryServiceFactoryInterface::class, Argument::type('callable'))->shouldBeCalled();
        $this->container->set(TelemetryServiceInterface::class, Argument::type('callable'))->shouldBeCalled();

        // Set expectations for container `get` method
        $this->container->get(LoggerProviderFactoryInterface::class)->willReturn($loggerProviderFactory->reveal());
        $this->container->get(TracerProviderFactoryInterface::class)->willReturn($tracerProviderFactory->reveal());
        $this->container->get(Client::class)->willReturn($client->reveal());
        $this->container->get(LoggerInterface::class)->willReturn($logger->reveal());
        $telemetryServiceFactory->create($this->configuration->reveal(), $logger->reveal())
            ->willReturn($telemetryService->reveal());

        $this->container->get(TelemetryServiceFactoryInterface::class)->willReturn($telemetryServiceFactory->reveal());

        // Run the register method
        $this->telemetryServiceProvider->register($this->container->reveal());

        // No need to explicitly check predictions, Prophecy will automatically fail the test if expectations aren't met
        $this->prophet->checkPredictions();
        $this->addToAssertionCount(1); // Adding a dummy assertion to avoid "no assertions performed" message
    }
}
