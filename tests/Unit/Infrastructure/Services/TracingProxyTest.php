<?php

namespace Unit\Infrastructure\Services;

use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\ScopeInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface;
use Webgrip\TelemetryService\Infrastructure\Services\TracingProxy;
use Webgrip\TelemetryService\Tests\Fixtures\TraceableClass;
use Webgrip\TelemetryService\Tests\Unit\TestCase;

class TracingProxyTest extends TestCase
{
    private ObjectProphecy|TelemetryServiceInterface $telemetryService;
    private ObjectProphecy|SpanBuilderInterface $spanBuilder;
    private ObjectProphecy|SpanInterface $span;
    private ObjectProphecy|ScopeInterface $scope;
    private ObjectProphecy|TracerInterface $tracer;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize all necessary mock objects
        $this->telemetryService = $this->prophesize(TelemetryServiceInterface::class);
        $this->spanBuilder = $this->prophesize(SpanBuilderInterface::class);
        $this->span = $this->prophesize(SpanInterface::class);
        $this->scope = $this->prophesize(ScopeInterface::class);
        $this->tracer = $this->prophesize(TracerInterface::class);

        // Mock Span to return a Scope when activated
        $this->span->activate()->willReturn($this->scope->reveal());

        // Configure SpanBuilder to return a mocked Span when starting a span
        $this->spanBuilder->startSpan()->willReturn($this->span->reveal());

        // Configure Tracer to return the SpanBuilder when building a span
        $this->tracer->spanBuilder(Argument::any())->willReturn($this->spanBuilder->reveal());

        // Mock the telemetryService to return our Tracer mock
        $this->telemetryService->tracer()->willReturn($this->tracer->reveal());
    }

    public function testMethodWithTracing()
    {
        // Create a TraceableClass instance and wrap it in the TracingProxy
        $traceableClass = new TraceableClass();
        $proxy = new TracingProxy($traceableClass, $this->telemetryService->reveal());

        $this->span->activate()->shouldBeCalled();
        $this->span->end()->shouldBeCalled();
        $this->scope->detach()->shouldBeCalled();

        // Call the traced method through the proxy
        $proxy->tracedMethod();

        // Verifies that all expected interactions occurred
        $this->telemetryService->tracer()->shouldHaveBeenCalled();
        $this->spanBuilder->startSpan()->shouldHaveBeenCalled();
        $this->span->activate()->shouldHaveBeenCalled();
        $this->scope->detach()->shouldHaveBeenCalled();
        $this->span->end()->shouldHaveBeenCalled();

        $this->prophet->checkPredictions();
        $this->addToAssertionCount(1);
    }

    public function testMethodWithoutTracing()
    {
        // Create a TraceableClass instance and wrap it in the TracingProxy
        $traceableClass = new class {
            public function untracedMethod()
            {
                // Simulate some method logic
            }
        };
        $proxy = new TracingProxy($traceableClass, $this->telemetryService->reveal());

        // Set the expectation that none of the tracing methods will be called
        $this->span->activate()->shouldNotBeCalled();
        $this->span->end()->shouldNotBeCalled();
        $this->scope->detach()->shouldNotBeCalled();

        // Call the untraced method through the proxy
        $proxy->untracedMethod();

        $this->prophet->checkPredictions();
        $this->addToAssertionCount(1);
    }

    public function testExceptionHandlingInTracedMethod()
    {
        // Define a TraceableClass with a method that throws an exception
        $traceableClass = new class {
            #[Traceable]
            public function methodThatThrows()
            {
                throw new \Exception("Test exception");
            }
        };

        $proxy = new TracingProxy($traceableClass, $this->telemetryService->reveal());

        // Set expectations on the span and scope objects
        $this->span->activate()->shouldBeCalled();
        $this->span->end()->shouldBeCalled();
        $this->scope->detach()->shouldBeCalled();

        // Expect the telemetry service to register an exception
        $this->telemetryService->registerException(Argument::type(\Throwable::class), $this->span->reveal())->shouldBeCalled();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Test exception");

        // Call the method that throws an exception
        $proxy->methodThatThrows();

        $this->prophet->checkPredictions();
        $this->addToAssertionCount(1);
    }
}