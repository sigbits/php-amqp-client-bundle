<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Endpoint;

interface SenderEndpointInterface
{
    public function open(): OpenSenderEndpoint;

    public function connect(): OpenSenderEndpoint;
}
