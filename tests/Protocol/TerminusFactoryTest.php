<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\Protocol;

use PHPUnit\Framework\TestCase;
use Sigbits\Amqp\Protocol\Performative\Accepted;
use Sigbits\Amqp\Protocol\Performative\Released;
use Sigbits\Amqp\Protocol\Performative\Source;
use Sigbits\Amqp\Protocol\Performative\Target;
use Sigbits\AmqpBundle\Protocol\TerminusFactory;

final class TerminusFactoryTest extends TestCase
{
    public function testCreatesClient13TerminusAndDeliveryStateValues(): void
    {
        $factory = new TerminusFactory();

        $source = $factory->source(
            address: '/queues/orders',
            capabilities: ['queue'],
            distributionMode: 'copy',
            outcomes: ['accepted', 'released'],
            defaultOutcome: $factory->released(),
        );
        $target = $factory->target(
            address: '/queues/orders',
            capabilities: ['queue'],
        );

        self::assertInstanceOf(Source::class, $source);
        self::assertSame('/queues/orders', $source->address);
        self::assertInstanceOf(Released::class, $source->defaultOutcome);
        self::assertInstanceOf(Target::class, $target);
        self::assertInstanceOf(Accepted::class, $factory->accepted());
    }
}
