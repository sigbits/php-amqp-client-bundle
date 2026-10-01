<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\Fixtures;

use Sigbits\AmqpBundle\Health\ConnectionHealthCheckerInterface;

final readonly class DefaultConnectionHealthCheckerConsumer
{
    public function __construct(
        private ConnectionHealthCheckerInterface $connectionHealthChecker,
    ) {
    }

    public function connectionHealthChecker(): ConnectionHealthCheckerInterface
    {
        return $this->connectionHealthChecker;
    }
}
