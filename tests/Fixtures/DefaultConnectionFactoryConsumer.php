<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\Fixtures;

use Sigbits\AmqpBundle\Connection\ConnectionFactoryInterface;

final readonly class DefaultConnectionFactoryConsumer
{
    public function __construct(
        private ConnectionFactoryInterface $connectionFactory,
    ) {
    }

    public function connectionFactory(): ConnectionFactoryInterface
    {
        return $this->connectionFactory;
    }
}
