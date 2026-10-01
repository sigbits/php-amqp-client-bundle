# Symfony AMQP Client Bundle

Symfony bundle for [`sigbits/php-amqp-client`](https://github.com/sigbits/php-amqp-client).

This project is in early development. The first milestone establishes the
package foundation; the first user-facing feature milestone will add thin
Dependency Injection configuration for AMQP client connections.

See [docs/roadmap.md](docs/roadmap.md) for planned milestones.

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
