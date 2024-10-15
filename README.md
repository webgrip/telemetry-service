#prerequisites

- PHP 8.2
- Composer


# How to use

### Serviceprovider
```php
TelemetryServiceProvider::class
```

### Using TelemetryService to log
```php
use \Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface;

class Foo
{
    public function __construct(private TelemetryServiceInterface $telemetryService) {
    }
    
    public function bar()
    {
        $this->telemetryService->logger()->debug('Hello World');
        $this->telemetryService->logger()->info('Hello World');
        $this->telemetryService->logger()->warning('Hello World');
        // ... 
    }
}
```


### Using TelemetryService to trace
```php
use \Webgrip\TelemetryService\Core\Domain\Services\TelemetryServiceInterface;

class Foo
{
    public function __construct(private TelemetryServiceInterface $telemetryService) {
    }
    
    public function bar()
    {
        $tracer = $this->telemetryService->tracer();
        $span = $tracer->spanBuilder('foo')->startSpan();
        
        $span->addEvent('bar');
        $span->setAttributes(['foo' => 'bar']);
        $span->recordException(new \Exception('Hello World'));
        $span->addLink('foo', 'bar');
        // ... 
        
        $span->end();
    }
}
```


### Using attributes
> You can add the attribute 'Webgrip\TelemetryService\Core\Domain\Services\Traceable' to your class to automatically trace all methods of the class
> You can also use this attribute to trace a single method

```php

#[\Webgrip\TelemetryService\Core\Domain\Attributes\Traceable]
class Foo
{
    public function bar()
    {
        // ...
    }
    
    #[\Webgrip\TelemetryService\Core\Domain\Attributes\Traceable]
    public function baz()
    {
        // ...
    }
}
```
}

// DI configuration
return [
    Foo::class => function (\DI\Container $container) {
        $factory = $container->get(\Webgrip\TelemetryService\Infrastructure\Factories\TracedClassFactory::class)
        $foo = new Foo();
        
        return $factory->create($foo);
    }
];
```
