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

interface MessageFactoryInterface
{
    /**
     * @param array<string, mixed> $deliveryAnnotations
     * @param array<string, mixed> $messageAnnotations
     * @param array<string, mixed> $applicationProperties
     * @param array<string, mixed> $footer
     * @param list<mixed> $bodySequence
     */
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
    ): Message;

    public function header(
        ?bool $durable = null,
        ?int $priority = null,
        ?int $ttl = null,
        ?bool $firstAcquirer = null,
        ?int $deliveryCount = null,
    ): Header;

    /**
     * @param string|ULong|Uuid|Binary|null $messageId
     * @param string|ULong|Uuid|Binary|null $correlationId
     */
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
    ): Properties;
}
