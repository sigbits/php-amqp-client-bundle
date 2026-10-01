# Symfony AMQP Client Bundle

Symfony bundle for [`sigbits/php-amqp-client`](https://github.com/sigbits/php-amqp-client).

This project is in early development. It currently provides a thin
Dependency Injection and configuration layer for AMQP client connection
factories.

See [docs/roadmap.md](docs/roadmap.md) for planned milestones and
[docs/configuration.md](docs/configuration.md) for the full configuration
reference.

## Configuration

The bundle registers AMQP connection factories. Fetching a factory from the
container does not open a network connection; the AMQP connection is opened
only when application code calls `connect()`.

```yaml
# config/packages/sigbits_amqp.yaml
sigbits_amqp:
  default_connection: default
  connections:
    default:
      uri: '%env(AMQP_URL)%'
      container_id: 'orders-api'
      timeout: 10.0
```

Inject the default factory by type:

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

Named factories are available through predictable service IDs:

```yaml
sigbits_amqp:
  default_connection: default
  connections:
    default:
      uri: '%env(AMQP_URL)%'
    analytics:
      uri: '%env(AMQP_ANALYTICS_URL)%'
      tls:
        peer_name: 'broker.example.com'
        cafile: '/etc/ssl/certs/broker-ca.pem'
      sasl:
        mechanism: plain
        username: '%env(AMQP_ANALYTICS_USER)%'
        password: '%env(AMQP_ANALYTICS_PASSWORD)%'
```

The service ID for the `analytics` factory is
`sigbits_amqp.connection_factory.analytics`.

For all options, defaults, TLS/SASL examples, environment-variable examples,
and invalid configuration messages, see
[docs/configuration.md](docs/configuration.md).

## Development

Local development commands run inside Docker containers:

```sh
make build
make install
make ci
```

Use `PHP_VERSION` to run against a specific supported PHP version:

```sh
PHP_VERSION=8.4 make ci
PHP_VERSION=8.5 make ci
```
