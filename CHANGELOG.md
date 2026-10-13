# Changelog

All notable changes to `sigbits/php-amqp-client-bundle` are documented here.

The project uses SemVer-style tags with a `v` prefix.

## v0.6.0

Client 1.3 convenience services.

- Raised the `sigbits/php-amqp-client` support baseline to `^1.3`.
- Updated the development branch alias to `0.6-dev`.
- Documented the planned `0.6.0` scope for client 1.3 convenience services.
- Recorded that Messenger integration remains optional and outside the core
  runtime dependency set.
- Added named sender and receiver endpoint services.
- Added message, AMQP type, terminus, and settlement helper services for the
  client 1.3 public value objects.

## v0.5.1

Documentation patch.

- Clarified in the README that Symfony Messenger integration is not included
  yet.
- Added a Packagist-visible documentation support link in Composer metadata.

## v0.5.0

Publish-readiness release.

- Added installation guidance to the README.
- Added a public API reference for stable bundle contracts and service IDs.
- Added release notes scaffolding for GitHub releases.
- Documented completed roadmap milestones and the first public release target.
- Polished Composer package metadata for Packagist.

## v0.4.0

Operational readiness milestone.

- Added connection health-check services.
- Documented request, CLI, and long-running worker connection lifecycle
  guidance.
- Documented release and Packagist publishing steps.
- Documented compatibility policy.

## v0.3.0

Developer ergonomics milestone.

- Added full configuration reference documentation.
- Added Symfony kernel tests for bundle loading, autowiring, and environment
  variable resolution.
- Updated README links to detailed documentation.

## v0.2.1

Compatibility patch.

- Fixed Symfony 6.4 static analysis compatibility in the configuration tree.

## v0.2.0

Thin DI/config milestone.

- Added `sigbits_amqp` configuration.
- Added named AMQP connection factory services.
- Added default factory aliases.
- Added validation for connection options.

## v0.1.0

Package foundation milestone.

- Created the bundle package skeleton.
- Added Docker-based local development commands.
- Added CI, PHPUnit, PHPStan, and PHP CS Fixer configuration.
- Added MIT license.
