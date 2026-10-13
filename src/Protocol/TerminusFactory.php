<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Protocol;

use Sigbits\Amqp\Protocol\Performative\Accepted;
use Sigbits\Amqp\Protocol\Performative\DeliveryState;
use Sigbits\Amqp\Protocol\Performative\Modified;
use Sigbits\Amqp\Protocol\Performative\Rejected;
use Sigbits\Amqp\Protocol\Performative\Released;
use Sigbits\Amqp\Protocol\Performative\Source;
use Sigbits\Amqp\Protocol\Performative\Target;
use Sigbits\Amqp\Protocol\Type\AmqpMap;

final readonly class TerminusFactory implements TerminusFactoryInterface
{
    public function source(
        ?string $address = null,
        ?int $durable = null,
        ?string $expiryPolicy = null,
        ?int $timeout = null,
        ?bool $dynamic = null,
        ?AmqpMap $dynamicNodeProperties = null,
        array $capabilities = [],
        ?string $distributionMode = null,
        array $outcomes = [],
        ?AmqpMap $filter = null,
        ?DeliveryState $defaultOutcome = null,
    ): Source {
        return new Source(
            address: $address,
            durable: $durable,
            expiryPolicy: $expiryPolicy,
            timeout: $timeout,
            dynamic: $dynamic,
            dynamicNodeProperties: $dynamicNodeProperties,
            capabilities: $capabilities,
            distributionMode: $distributionMode,
            outcomes: $outcomes,
            filter: $filter,
            defaultOutcome: $defaultOutcome,
        );
    }

    public function target(
        ?string $address = null,
        ?int $durable = null,
        ?string $expiryPolicy = null,
        ?int $timeout = null,
        ?bool $dynamic = null,
        ?AmqpMap $dynamicNodeProperties = null,
        array $capabilities = [],
    ): Target {
        return new Target(
            address: $address,
            durable: $durable,
            expiryPolicy: $expiryPolicy,
            timeout: $timeout,
            dynamic: $dynamic,
            dynamicNodeProperties: $dynamicNodeProperties,
            capabilities: $capabilities,
        );
    }

    public function accepted(): Accepted
    {
        return new Accepted();
    }

    public function released(): Released
    {
        return new Released();
    }

    public function rejected(): Rejected
    {
        return new Rejected();
    }

    public function modified(
        ?bool $deliveryFailed = null,
        ?bool $undeliverableHere = null,
        ?AmqpMap $messageAnnotations = null,
    ): Modified {
        return new Modified(
            deliveryFailed: $deliveryFailed,
            undeliverableHere: $undeliverableHere,
            messageAnnotations: $messageAnnotations,
        );
    }
}
