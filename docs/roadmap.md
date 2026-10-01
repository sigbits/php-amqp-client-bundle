# Roadmap

This roadmap keeps the bundle small at first: it should make
`sigbits/php-amqp-client` comfortable to configure through Symfony's service
container before it grows optional integrations.

## Version Support Policy

The bundle supports the currently maintained Symfony branches that can run with
the PHP requirements of `sigbits/php-amqp-client`.

- PHP: `^8.3`
- Symfony: `^6.4 || ^7.4 || ^8.1`
- AMQP client: `sigbits/php-amqp-client` `^1.0`

Symfony 8.1 requires PHP 8.4 or newer, so its CI jobs run on PHP 8.4+ even
though the package-level PHP floor remains 8.3.

## 0.1.0 Package Foundation

Goal: publish a valid Symfony bundle package skeleton with the same engineering
baseline as `sigbits/php-amqp-client`.

- Composer package metadata for `sigbits/php-amqp-client-bundle`.
- MIT license with the yearless Sigbits copyright notice.
- Symfony bundle type and Flex-friendly package metadata.
- PHP 8.3+ and maintained Symfony component constraints.
- PSR-4 autoloading for `Sigbits\AmqpBundle`.
- Minimal bundle class.
- PHPUnit, PHPStan, and PHP CS Fixer configuration.
- GitHub Actions CI for Composer validation, dependency installation, coding
  standards, static analysis, and tests.

Exit criteria:

- `composer validate --strict --no-check-lock` succeeds.
- `composer cs` succeeds.
- `composer stan` succeeds.
- `composer test` succeeds.
- CI runs the same checks on supported PHP/Symfony combinations.

## 0.2.0 Thin DI/Config Bundle

Goal: let applications declare named AMQP client connections in Symfony config
and consume them as services.

- Add a bundle extension and configuration tree.
- Support a default connection and multiple named connections.
- Register connection factory services without opening network connections at
  container compile time.
- Expose predictable service IDs and aliases.
- Validate URI, container ID, timeout, TLS, and SASL configuration.
- Keep public service contracts narrow so Messenger integration can reuse them
  later.

Exit criteria:

- Functional container tests cover valid and invalid configuration.
- Documentation shows single-connection and multi-connection examples.
- No Messenger-specific configuration is exposed.

## 0.3.0 Developer Ergonomics

Goal: make the bundle easy to adopt and debug in Symfony applications.

- Expand README and usage documentation.
- Add configuration reference documentation.
- Add tested examples for environment-variable based configuration.
- Improve exception messages for invalid config.
- Add Symfony kernel tests across maintained Symfony branches.

Exit criteria:

- New users can configure one connection from documentation alone.
- Invalid configuration failures point to the exact option that needs changing.

## 0.4.0 Operational Readiness

Goal: document and support production-oriented usage without broadening the
bundle into a worker framework.

- Document connection lifecycle guidance for requests, CLI commands, and
  long-running workers.
- Add optional health-check style services if they can be implemented without
  opening connections during container compilation.
- Document release and Packagist publishing process.
- Document compatibility policy for Symfony and the AMQP client.

Exit criteria:

- Operations guidance covers common deployment and worker lifecycle questions.
- Release documentation is sufficient to tag and publish a new version
  repeatably.

## Later: Messenger Integration

Messenger integration is intentionally out of scope for the first release line.
When added, it should reuse the same connection configuration and factory
contracts from the thin bundle instead of introducing a parallel configuration
model.

Likely scope:

- Sender and receiver transport factories.
- DSN parsing that maps to the existing bundle configuration.
- Message serialization guidance.
- Explicit retry and acknowledgement behavior aligned with the AMQP client's
  settlement model.
