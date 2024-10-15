<?php

namespace Webgrip\TelemetryService\Tests\Fixtures;

use Webgrip\TelemetryService\Core\Domain\Attributes\Traceable;

class PartiallyTraceableClass
{
    #[Traceable]
    public function tracedMethod()
    {
        // Simulate some logic
    }

    public function untracedMethod()
    {
        // Simulate some logic
    }
}