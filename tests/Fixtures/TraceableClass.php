<?php

namespace Webgrip\TelemetryService\Tests\Fixtures;

use Webgrip\TelemetryService\Core\Domain\Attributes\Traceable;

#[Traceable]
class TraceableClass
{
    public function tracedMethod()
    {
        return 'traced';
    }
}