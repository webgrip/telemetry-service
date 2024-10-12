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
