<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Endpoint;

use Sigbits\AmqpBundle\Connection\ConnectionFactoryInterface;

final readonly class ReceiverEndpoint implements ReceiverEndpointInterface
{
    public function __construct(
        private ConnectionFactoryInterface $connectionFactory,
        private string $address,
        private string $name,
        private int $handle,
        private int $credit,
        private int $receiveTimeoutMilliseconds,
    ) {
    }

    public function open(): OpenReceiverEndpoint
    {
        return $this->connect();
    }

    public function connect(): OpenReceiverEndpoint
    {
        $connection = $this->connectionFactory->connect();
        $session = $connection->beginSession();
        $receiver = $session->openReceiver($this->address, $this->name, $this->handle, $this->credit);

        return new OpenReceiverEndpoint($connection, $session, $receiver, $this->receiveTimeoutMilliseconds);
    }
}
