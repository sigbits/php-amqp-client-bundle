<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Endpoint;

use Sigbits\Amqp\Client\Connection;
use Sigbits\Amqp\Client\Delivery;
use Sigbits\Amqp\Client\Receiver;
use Sigbits\Amqp\Client\Session;
use Sigbits\Amqp\Protocol\Message\Message;

final readonly class OpenReceiverEndpoint
{
    public function __construct(
        private Connection $connection,
        private Session $session,
        private Receiver $receiver,
        private int $receiveTimeoutMilliseconds,
    ) {
    }

    public function receive(?int $timeoutMilliseconds = null): ?Message
    {
        return $this->receiver->receive($timeoutMilliseconds ?? $this->receiveTimeoutMilliseconds);
    }

    public function receiveDelivery(?int $timeoutMilliseconds = null): ?Delivery
    {
        return $this->receiver->receiveDelivery($timeoutMilliseconds ?? $this->receiveTimeoutMilliseconds);
    }

    /**
     * @return iterable<Delivery>
     */
    public function deliveries(?int $timeoutMilliseconds = null): iterable
    {
        return $this->receiver->deliveries($timeoutMilliseconds ?? $this->receiveTimeoutMilliseconds);
    }

    public function grantCredit(int $credit): void
    {
        $this->receiver->grantCredit($credit);
    }

    public function close(): void
    {
        try {
            $this->receiver->detach();
        } finally {
            try {
                $this->session->end();
            } finally {
                $this->connection->close();
            }
        }
    }
}
