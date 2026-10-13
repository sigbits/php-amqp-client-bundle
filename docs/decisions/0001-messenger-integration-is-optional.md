# Decision: Messenger Integration Is Optional

Symfony Messenger integration must remain optional for this bundle.

The core bundle depends on `sigbits/php-amqp-client`, Symfony configuration,
dependency injection, and kernel components. It must not require
`symfony/messenger` for connection factories, health checks, endpoint services,
message builders, link helpers, or settlement helpers.

If Messenger support is added later, it must activate only when
`symfony/messenger` is installed and explicit Messenger transport configuration
is present. It must reuse the bundle's core connection and endpoint
configuration instead of introducing a second AMQP connection model.

This keeps the bundle useful for applications that want direct AMQP client
control while still leaving room for an optional Messenger bridge.
