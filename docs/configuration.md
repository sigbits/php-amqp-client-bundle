# Configuration

The bundle exposes a small Dependency Injection layer around
`sigbits/php-amqp-client`. It registers lazy connection factory services;
no network connection is opened while Symfony builds the container.

## Requirements

- PHP: `^8.3`
- Symfony components: `^6.4 || ^7.4 || ^8.1`
- AMQP client: `sigbits/php-amqp-client` `^1.0`

Symfony 8.1 requires PHP 8.4.1 or newer. The package PHP floor remains 8.3
because Symfony 6.4 and 7.4 still run on PHP 8.3.

## Basic Setup

Create `config/packages/sigbits_amqp.yaml`:

```yaml
sigbits_amqp:
  connections:
    default:
      uri: '%env(AMQP_URL)%'
```

Then inject the default connection factory by type:

```php
<?php

use Sigbits\AmqpBundle\Connection\ConnectionFactoryInterface;

final readonly class OrderPublisher
{
    public function __construct(
        private ConnectionFactoryInterface $connectionFactory,
    ) {
    }

    public function publish(string $payload): void
    {
        $connection = $this->connectionFactory->connect();
        $session = $connection->beginSession();
        $sender = $session->openSender('/queues/orders');

        $sender->send($payload);

        $sender->detach();
        $session->end();
        $connection->close();
    }
}
```

Calling `connect()` opens the AMQP connection. Fetching the factory service
from the container does not.

## Full Reference

```yaml
sigbits_amqp:
  default_connection: default
  connections:
    default:
      uri: '%env(AMQP_URL)%'
      container_id: 'sigbits-php-amqp-client'
      timeout: 30.0
      tls:
        verify_peer: true
        verify_peer_name: true
        peer_name: null
        cafile: null
        local_cert: null
      sasl:
        mechanism: auto
        username: ''
        password: ''
        authorization_id: ''
```

### Root Options

| Option | Type | Default | Description |
| --- | --- | --- | --- |
| `default_connection` | string | `default` | Connection name used for `ConnectionFactoryInterface` and `sigbits_amqp.connection_factory`. |
| `connections` | map | required | Named connection definitions keyed by connection name. |

Connection names may contain letters, numbers, underscores, dots, and hyphens.
They are used in service IDs, so keep them stable.

### Connection Options

| Option | Type | Default | Description |
| --- | --- | --- | --- |
| `uri` | string | required | AMQP or AMQPS URI passed to the client when `connect()` is called. |
| `container_id` | string | `sigbits-php-amqp-client` | AMQP container ID sent by the client. |
| `timeout` | float | `30.0` | Connection timeout in seconds. Must be greater than `0`. |
| `tls` | map or omitted | omitted | TLS options for secure transports. |
| `sasl` | map | `mechanism: auto` | SASL authentication options. |

### TLS Options

```yaml
sigbits_amqp:
  connections:
    default:
      uri: '%env(AMQP_URL)%'
      tls:
        verify_peer: true
        verify_peer_name: true
        peer_name: 'broker.example.com'
        cafile: '/etc/ssl/certs/broker-ca.pem'
        local_cert: '/etc/ssl/private/client.pem'
```

| Option | Type | Default | Description |
| --- | --- | --- | --- |
| `verify_peer` | bool | `true` | Verify the broker certificate. |
| `verify_peer_name` | bool | `true` | Verify the broker certificate name. |
| `peer_name` | string or null | `null` | Expected broker certificate name. |
| `cafile` | string or null | `null` | Certificate authority file path. |
| `local_cert` | string or null | `null` | Client certificate file path. |

### SASL Options

```yaml
sigbits_amqp:
  connections:
    default:
      uri: '%env(AMQP_URL)%'
      sasl:
        mechanism: plain
        username: '%env(AMQP_USER)%'
        password: '%env(AMQP_PASSWORD)%'
        authorization_id: ''
```

| Option | Type | Default | Description |
| --- | --- | --- | --- |
| `mechanism` | `auto`, `anonymous`, or `plain` | `auto` | SASL strategy used by the client. |
| `username` | string | `''` | Required when `mechanism` is `plain`. |
| `password` | string | `''` | Password for `plain` authentication. |
| `authorization_id` | string | `''` | Optional SASL authorization identity. |

Use `auto` when the URI or broker setup should decide the SASL behavior. Use
`anonymous` for anonymous authentication. Use `plain` when the broker requires a
username and password outside the URI.

## Multiple Connections

```yaml
sigbits_amqp:
  default_connection: default
  connections:
    default:
      uri: '%env(AMQP_URL)%'
    analytics:
      uri: '%env(AMQP_ANALYTICS_URL)%'
      container_id: 'analytics-worker'
      timeout: 5.0
```

The default connection is available through these aliases:

- `Sigbits\AmqpBundle\Connection\ConnectionFactoryInterface`
- `sigbits_amqp.connection_factory`

Named factories use this service ID pattern:

```text
sigbits_amqp.connection_factory.<name>
```

For the `analytics` connection above, the service ID is:

```text
sigbits_amqp.connection_factory.analytics
```

## Health-Check Services

The bundle registers health-check services for configured connections. These
services are lazy: a broker connection is opened only when `check()` is called.

The default health checker is available through these aliases:

- `Sigbits\AmqpBundle\Health\ConnectionHealthCheckerInterface`
- `sigbits_amqp.connection_health_checker`

Named health checkers use this service ID pattern:

```text
sigbits_amqp.connection_health_checker.<name>
```

For the `analytics` connection above, the service ID is:

```text
sigbits_amqp.connection_health_checker.analytics
```

## Environment Variables

The bundle accepts Symfony environment placeholders in any string option:

```yaml
sigbits_amqp:
  connections:
    default:
      uri: '%env(AMQP_URL)%'
      container_id: '%env(AMQP_CONTAINER_ID)%'
      sasl:
        mechanism: plain
        username: '%env(AMQP_USER)%'
        password: '%env(AMQP_PASSWORD)%'
```

Example `.env` values:

```dotenv
AMQP_URL=amqps://broker.example.com:5671
AMQP_CONTAINER_ID=orders-api
AMQP_USER=orders
AMQP_PASSWORD=secret
```

## Invalid Configuration

Symfony reports invalid configuration during container compilation. Common
failures include:

| Problem | Message Includes |
| --- | --- |
| `default_connection` references a missing connection | `sigbits_amqp.default_connection` |
| Connection name contains unsupported characters | `connection name` and the invalid name |
| `timeout` is `0` or negative | `The timeout must be greater than 0.` |
| `sasl.mechanism` is `plain` without a username | `sasl.username` |

Messenger transport configuration is intentionally not part of this bundle
version. Future Messenger integration should reuse these connection factories
instead of introducing a second connection model.
