<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Health;

use Sigbits\AmqpBundle\Connection\ConnectionFactoryInterface;

final readonly class ConnectionHealthChecker implements ConnectionHealthCheckerInterface
{
    public function __construct(
        private ConnectionFactoryInterface $connectionFactory,
    ) {
    }

    public function check(): ConnectionHealthCheckResult
    {
        try {
            $connection = $this->connectionFactory->connect();
        } catch (\Throwable $exception) {
            return ConnectionHealthCheckResult::unhealthy($exception);
        }

        try {
            $connection->close();
        } catch (\Throwable $exception) {
            return ConnectionHealthCheckResult::unhealthy($exception);
        }

        return ConnectionHealthCheckResult::healthy();
    }
}
