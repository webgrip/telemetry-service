<?php

namespace Webgrip\TelemetryService\Tests\Unit;

use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Prophet $prophet;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->prophet = new Prophet();
        parent::setUp();
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @param string $classOrInterface
     * @return ObjectProphecy
     * @template T of object
     * @phpstan-param class-string<T>|null $classOrInterface
     * @phpstan-return ($classOrInterface is null ? ObjectProphecy<object> : ObjectProphecy<T>)
     */
    protected function prophesize(string $classOrInterface): object
    {
        return $this->prophet->prophesize($classOrInterface);
    }
}
