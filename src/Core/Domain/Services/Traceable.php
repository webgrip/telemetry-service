<?php

namespace Webgrip\TelemetryService\Core\Domain\Services;

use \Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Traceable
{
    public function __construct(
        public ?string $operationName = null
    ) {}
}
