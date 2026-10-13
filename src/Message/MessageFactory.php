<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Message;

use Sigbits\Amqp\Protocol\Message\Header;
use Sigbits\Amqp\Protocol\Message\Message;
use Sigbits\Amqp\Protocol\Message\MessageBodySection;
use Sigbits\Amqp\Protocol\Message\Properties;
use Sigbits\Amqp\Protocol\Message\UnsetMessageBodyValue;
use Sigbits\Amqp\Protocol\Type\Binary;
use Sigbits\Amqp\Protocol\Type\Timestamp;
use Sigbits\Amqp\Protocol\Type\UInt;
use Sigbits\Amqp\Protocol\Type\ULong;
use Sigbits\Amqp\Protocol\Type\Uuid;

final readonly class MessageFactory implements MessageFactoryInterface
{
    public function message(
        string $body = '',
        MessageBodySection $bodySection = MessageBodySection::Data,
        array $bodySequence = [],
        ?Header $header = null,
        array $deliveryAnnotations = [],
        array $messageAnnotations = [],
        ?Properties $properties = null,
        array $applicationProperties = [],
        array $footer = [],
        mixed $bodyValue = new UnsetMessageBodyValue(),
    ): Message {
        return new Message(
            body: $body,
            bodySection: $bodySection,
            bodySequence: $bodySequence,
            header: $header,
            deliveryAnnotations: $deliveryAnnotations,
            messageAnnotations: $messageAnnotations,
            properties: $properties,
            applicationProperties: $applicationProperties,
            footer: $footer,
            bodyValue: $bodyValue,
        );
    }

    public function header(
        ?bool $durable = null,
        ?int $priority = null,
        ?int $ttl = null,
        ?bool $firstAcquirer = null,
        ?int $deliveryCount = null,
    ): Header {
        return new Header(
            durable: $durable,
            priority: $priority,
            ttl: $ttl,
            firstAcquirer: $firstAcquirer,
            deliveryCount: $deliveryCount,
        );
    }

    public function properties(
        string|ULong|Uuid|Binary|null $messageId = null,
        string|ULong|Uuid|Binary|null $correlationId = null,
        ?string $contentType = null,
        ?string $subject = null,
        ?Binary $userId = null,
        ?string $to = null,
        ?string $replyTo = null,
        ?string $contentEncoding = null,
        ?Timestamp $absoluteExpiryTime = null,
        ?Timestamp $creationTime = null,
        ?string $groupId = null,
        ?UInt $groupSequence = null,
        ?string $replyToGroupId = null,
    ): Properties {
        return new Properties(
            messageId: $messageId,
            correlationId: $correlationId,
            contentType: $contentType,
            subject: $subject,
            userId: $userId,
            to: $to,
            replyTo: $replyTo,
            contentEncoding: $contentEncoding,
            absoluteExpiryTime: $absoluteExpiryTime,
            creationTime: $creationTime,
            groupId: $groupId,
            groupSequence: $groupSequence,
            replyToGroupId: $replyToGroupId,
        );
    }
}
