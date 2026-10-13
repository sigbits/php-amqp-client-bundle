<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Settlement;

use Sigbits\Amqp\Client\Delivery;
use Sigbits\Amqp\Protocol\Performative\SettlementOutcome;

interface SettlementHelperInterface
{
    public function settle(Delivery $delivery, SettlementOutcome $outcome): void;

    public function accept(Delivery $delivery): void;

    public function release(Delivery $delivery): void;

    public function reject(Delivery $delivery): void;
}
