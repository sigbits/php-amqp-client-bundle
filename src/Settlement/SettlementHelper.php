<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Settlement;

use Sigbits\Amqp\Client\Delivery;
use Sigbits\Amqp\Protocol\Performative\SettlementOutcome;

final readonly class SettlementHelper implements SettlementHelperInterface
{
    public function settle(Delivery $delivery, SettlementOutcome $outcome): void
    {
        match ($outcome) {
            SettlementOutcome::Accepted => $delivery->accept(),
            SettlementOutcome::Released => $delivery->release(),
            SettlementOutcome::Rejected => $delivery->reject(),
        };
    }

    public function accept(Delivery $delivery): void
    {
        $delivery->accept();
    }

    public function release(Delivery $delivery): void
    {
        $delivery->release();
    }

    public function reject(Delivery $delivery): void
    {
        $delivery->reject();
    }
}
