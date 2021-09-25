# Closure Handler Middleware

PSR-15 compatible HTTP middleware which can utilize closure type request handlers.

If you don't want to bother about those ugly HTTP controllers and love simplicity,
this middleware is your best friend.

## Requirements

* PHP >=8.0

## Usage

```php
$request = $serverRequestFactory
    ->createServerRequest('GET', '/')
    ->withAttribute('__handler', fn() => 'hello world!');

$middleware = new ClosureHandler(new HttpResponder());

$response = $middleware->process($request, $nextHandler);

echo $response->getBody(); // Will print "hello world!"
```

If no handler found in the request object, or handler is not closure, then
the middleware passes the request to the next middleware in the row.

Name of the request attribute (`__handler`) can be changed by the
`watchRequestAttribute()` method.
