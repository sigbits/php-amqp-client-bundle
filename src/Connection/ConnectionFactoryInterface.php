<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Connection;

use Sigbits\Amqp\Client\Connection;

interface ConnectionFactoryInterface
{
    public function connect(): Connection;
}
