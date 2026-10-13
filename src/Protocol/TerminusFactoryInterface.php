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

interface TerminusFactoryInterface
{
    /**
     * @param list<string> $capabilities
     * @param list<string> $outcomes
     */
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
    ): Source;

    /**
     * @param list<string> $capabilities
     */
    public function target(
        ?string $address = null,
        ?int $durable = null,
        ?string $expiryPolicy = null,
        ?int $timeout = null,
        ?bool $dynamic = null,
        ?AmqpMap $dynamicNodeProperties = null,
        array $capabilities = [],
    ): Target;

    public function accepted(): Accepted;

    public function released(): Released;

    public function rejected(): Rejected;

    public function modified(
        ?bool $deliveryFailed = null,
        ?bool $undeliverableHere = null,
        ?AmqpMap $messageAnnotations = null,
    ): Modified;
}
