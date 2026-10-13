<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\Message;

use PHPUnit\Framework\TestCase;
use Sigbits\Amqp\Protocol\Message\MessageBodySection;
use Sigbits\Amqp\Protocol\Type\Binary;
use Sigbits\Amqp\Protocol\Type\Timestamp;
use Sigbits\AmqpBundle\Message\MessageFactory;

final class MessageFactoryTest extends TestCase
{
    public function testCreatesMessageWithClient13BodyValueAndProperties(): void
    {
        $factory = new MessageFactory();

        $message = $factory->message(
            body: '',
            bodySection: MessageBodySection::AmqpValue,
            bodyValue: ['event' => 'created'],
            properties: $factory->properties(
                messageId: 'order-123',
                correlationId: new Binary('correlation-bytes'),
                contentType: 'application/json',
                to: '/queues/orders',
                creationTime: new Timestamp(1_700_000_000_000),
            ),
            applicationProperties: [
                'source' => 'checkout',
            ],
        );

        self::assertTrue($message->hasBodyValue());
        self::assertSame(['event' => 'created'], $message->bodyValue);
        self::assertSame('order-123', $message->properties->messageId);
        self::assertInstanceOf(Binary::class, $message->properties->correlationId);
        self::assertSame('checkout', $message->applicationProperties['source']);
    }
}
