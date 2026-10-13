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
- `senders`
- `receivers`

Connection definitions support:

- `uri`
- `container_id`
- `timeout`
- `tls`
- `sasl`

Sender endpoint definitions support:

- `connection`
- `address`
- `link_name`
- `handle`
- `max_frame_size`

Receiver endpoint definitions support:

- `connection`
- `address`
- `link_name`
- `handle`
- `credit`
- `receive_timeout`

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

## Endpoint Services

Sender endpoints implement:

```php
<?php

namespace Sigbits\AmqpBundle\Endpoint;

interface SenderEndpointInterface
{
    public function open(): OpenSenderEndpoint;

    public function connect(): OpenSenderEndpoint;
}
```

Named sender endpoints use this service ID pattern:

```text
sigbits_amqp.sender_endpoint.<name>
```

Receiver endpoints implement:

```php
<?php

namespace Sigbits\AmqpBundle\Endpoint;

interface ReceiverEndpointInterface
{
    public function open(): OpenReceiverEndpoint;

    public function connect(): OpenReceiverEndpoint;
}
```

Named receiver endpoints use this service ID pattern:

```text
sigbits_amqp.receiver_endpoint.<name>
```

Opening or connecting an endpoint opens a connection, begins a session, and
opens the AMQP link. `connect()` is an alias for `open()`. Applications are
responsible for calling `close()` on the returned open endpoint object when the
unit of work is finished.

Open receiver endpoints expose:

```php
<?php

namespace Sigbits\AmqpBundle\Endpoint;

use Sigbits\Amqp\Client\Delivery;
use Sigbits\Amqp\Protocol\Message\Message;

final readonly class OpenReceiverEndpoint
{
    public function receive(?int $timeoutMilliseconds = null): ?Message;

    public function receiveDelivery(?int $timeoutMilliseconds = null): ?Delivery;

    /**
     * @return iterable<Delivery>
     */
    public function deliveries(?int $timeoutMilliseconds = null): iterable;

    public function grantCredit(int $credit): void;

    public function close(): void;
}
```

`deliveries()` yields until the configured receive timeout, or the timeout
passed to the method, returns no delivery.

## Client 1.4 Helper Services

The default AMQP message factory is available as:

```text
Sigbits\AmqpBundle\Message\MessageFactoryInterface
sigbits_amqp.message_factory
```

The AMQP generic type factory is available as:

```text
Sigbits\AmqpBundle\Protocol\AmqpTypeFactoryInterface
sigbits_amqp.amqp_type_factory
```

The AMQP terminus and delivery-state value factory is available as:

```text
Sigbits\AmqpBundle\Protocol\TerminusFactoryInterface
sigbits_amqp.terminus_factory
```

`TerminusFactoryInterface` includes delivery-state factories for accepted,
released, rejected, and modified outcomes.

The settlement helper is available as:

```text
Sigbits\AmqpBundle\Settlement\SettlementHelperInterface
sigbits_amqp.settlement_helper
```

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
- Open endpoint value-object constructor signatures.
- Test fixtures.
- Private methods.
- Container definition internals.

Applications should depend on the interfaces, configuration keys, and service
IDs listed in this document.

## Messenger

Messenger integration is optional and is not part of this public API yet.
Future Messenger support should activate only when `symfony/messenger` is
installed and explicit Messenger transport configuration is present. It should
reuse the connection and endpoint contracts documented here.
