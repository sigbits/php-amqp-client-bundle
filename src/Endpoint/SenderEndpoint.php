<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Endpoint;

use Sigbits\AmqpBundle\Connection\ConnectionFactoryInterface;

final readonly class SenderEndpoint implements SenderEndpointInterface
{
    public function __construct(
        private ConnectionFactoryInterface $connectionFactory,
        private string $address,
        private string $name,
        private int $handle,
        private int $maxFrameSize,
    ) {
    }

    public function open(): OpenSenderEndpoint
    {
        return $this->connect();
    }

    public function connect(): OpenSenderEndpoint
    {
        $connection = $this->connectionFactory->connect();
        $session = $connection->beginSession();
        $sender = $session->openSender($this->address, $this->name, $this->handle);

        return new OpenSenderEndpoint($connection, $session, $sender, $this->maxFrameSize);
    }
}
