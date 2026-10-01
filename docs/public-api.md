# Public API

This document defines the bundle surface that applications may rely on before
Messenger integration is added.

## Configuration Alias

The bundle configuration alias is:

```text
sigbits_amqp
```

Applications configure the bundle in `config/packages/sigbits_amqp.yaml`.

## Configuration Keys

The supported configuration keys are documented in
[`docs/configuration.md`](configuration.md).

The root keys are:

- `default_connection`
- `connections`

Connection definitions support:

- `uri`
- `container_id`
- `timeout`
- `tls`
- `sasl`

## Connection Factories

The public connection factory contract is:

```php
<?php

namespace Sigbits\AmqpBundle\Connection;

use Sigbits\Amqp\Client\Connection;

interface ConnectionFactoryInterface
{
    public function connect(): Connection;
}
```

Fetching a factory from the Symfony container must not open a network
connection. Calling `connect()` opens the AMQP connection.

The default connection factory is available as:

```text
Sigbits\AmqpBundle\Connection\ConnectionFactoryInterface
sigbits_amqp.connection_factory
```

Named connection factories use this service ID pattern:

```text
sigbits_amqp.connection_factory.<name>
```

## Health Checks

The public health-check contract is:

```php
<?php

namespace Sigbits\AmqpBundle\Health;

interface ConnectionHealthCheckerInterface
{
    public function check(): ConnectionHealthCheckResult;
}
```

The default health checker is available as:

```text
Sigbits\AmqpBundle\Health\ConnectionHealthCheckerInterface
sigbits_amqp.connection_health_checker
```

Named health checkers use this service ID pattern:

```text
sigbits_amqp.connection_health_checker.<name>
```

Calling `check()` opens an AMQP connection. A successful check closes that
connection before returning.

## Result Objects

`ConnectionHealthCheckResult` exposes:

- `isHealthy(): bool`
- `errorMessage(): ?string`
- `exceptionClass(): ?string`

Applications should treat result objects as read-only values.

## Internal Surface

These are implementation details and may change between pre-1.0 minor releases:

- Concrete service classes.
- Constructor signatures of concrete service classes.
- Test fixtures.
- Private methods.
- Container definition internals.

Applications should depend on the interfaces, configuration keys, and service
IDs listed in this document.

## Messenger

Messenger integration is not part of this public API yet. Future Messenger
support should reuse the connection configuration and factory contracts
documented here.
