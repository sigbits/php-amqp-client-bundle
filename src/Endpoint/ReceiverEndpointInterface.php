<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Endpoint;

interface ReceiverEndpointInterface
{
    public function open(): OpenReceiverEndpoint;

    public function connect(): OpenReceiverEndpoint;
}
