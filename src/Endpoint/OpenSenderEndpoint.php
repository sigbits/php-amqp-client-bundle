<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Endpoint;

use Sigbits\Amqp\Client\Connection;
use Sigbits\Amqp\Client\Sender;
use Sigbits\Amqp\Client\Session;
use Sigbits\Amqp\Protocol\Message\Message;

final readonly class OpenSenderEndpoint
{
    public function __construct(
        private Connection $connection,
        private Session $session,
        private Sender $sender,
        private int $maxFrameSize,
    ) {
    }

    public function send(Message|string $message, ?int $maxFrameSize = null): void
    {
        $this->sender->send($message, $maxFrameSize ?? $this->maxFrameSize);
    }

    public function close(): void
    {
        try {
            $this->sender->detach();
        } finally {
            try {
                $this->session->end();
            } finally {
                $this->connection->close();
            }
        }
    }
}
