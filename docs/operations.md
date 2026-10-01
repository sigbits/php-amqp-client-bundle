# Operations

This bundle stays deliberately small: it gives Symfony applications configured
connection factories and health-check helpers, but it does not manage worker
processes, retries, supervision, or Messenger transports.

## Connection Lifecycle

Connection factories are lazy. Symfony can build the container, warm the cache,
and fetch a factory service without opening a network connection. The AMQP
connection opens only when application code calls `connect()`.

Always close a connection when the unit of work is done:

```php
<?php

use Sigbits\AmqpBundle\Connection\ConnectionFactoryInterface;

final readonly class PublishOrderConfirmation
{
    public function __construct(
        private ConnectionFactoryInterface $connectionFactory,
    ) {
    }

    public function __invoke(string $payload): void
    {
        $connection = $this->connectionFactory->connect();

        try {
            $session = $connection->beginSession();
            $sender = $session->openSender('/queues/order-confirmations');

            $sender->send($payload);

            $sender->detach();
            $session->end();
        } finally {
            $connection->close();
        }
    }
}
```

## HTTP Requests

For request/response work, open the connection as late as possible and close it
before the request returns. Do not keep AMQP connections in shared services for
reuse across requests; PHP request lifecycles and process managers make that
hard to reason about.

Prefer a small application service that accepts payload data, opens a
connection, publishes or receives, and closes the connection in a `finally`
block.

## CLI Commands

For short CLI commands, use the same pattern as HTTP requests: connect inside
the command handler and close before the command exits.

For batch commands, avoid holding a connection longer than necessary. If a batch
does independent units of work, it is usually safer to reconnect between
chunks than to assume one connection will remain valid for the whole process.

## Long-Running Workers

Long-running workers need explicit lifecycle handling. This bundle does not
start, supervise, or restart workers.

Recommended worker rules:

- Connect during worker startup or immediately before the first AMQP operation.
- Close the connection during graceful shutdown.
- Catch AMQP/client exceptions at the unit-of-work boundary.
- Reconnect after connection-level failures instead of reusing a connection
  whose state is unclear.
- Let the process supervisor restart the worker after repeated failures.

Keep retry and acknowledgement behavior close to the worker code that owns the
business workflow. Future Messenger integration should reuse the same
connection factories, but it should define its own transport-level settlement
and retry semantics.

## Health Checks

The bundle registers health-check services next to the connection factories.
They do not open connections during container compilation. A connection is
opened only when `check()` is called, and a successful probe closes the
connection before returning.

Inject the default checker by interface:

```php
<?php

use Sigbits\AmqpBundle\Health\ConnectionHealthCheckerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class AmqpHealthController
{
    public function __construct(
        private ConnectionHealthCheckerInterface $healthChecker,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $result = $this->healthChecker->check();

        return new JsonResponse(
            [
                'healthy' => $result->isHealthy(),
                'error' => $result->errorMessage(),
                'exception_class' => $result->exceptionClass(),
            ],
            $result->isHealthy() ? 200 : 503,
        );
    }
}
```

Named checker services use this pattern:

```text
sigbits_amqp.connection_health_checker.<name>
```

The default checker is also available as:

```text
sigbits_amqp.connection_health_checker
```

Health checks are active probes. Use conservative timeouts in connection
configuration so failed broker checks return quickly enough for your platform.

## Compatibility Policy

The bundle supports maintained Symfony branches that are compatible with the
PHP requirement of `sigbits/php-amqp-client`.

Current support:

- PHP: `^8.3`
- Symfony: `^6.4 || ^7.4 || ^8.1`
- AMQP client: `sigbits/php-amqp-client` `^1.0`

Symfony 8.1 requires PHP 8.4.1 or newer, so Symfony 8.1 CI jobs run on PHP
8.4+ even though the package-level PHP floor remains 8.3.

Compatibility changes should be reflected in `composer.json`, CI, and this
document in the same pull request.
