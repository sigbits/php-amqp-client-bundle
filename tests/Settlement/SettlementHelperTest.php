<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\Settlement;

use PHPUnit\Framework\TestCase;
use Sigbits\Amqp\Client\Delivery;
use Sigbits\Amqp\Protocol\Message\Message;
use Sigbits\Amqp\Protocol\Performative\SettlementOutcome;
use Sigbits\AmqpBundle\Settlement\SettlementHelper;

final class SettlementHelperTest extends TestCase
{
    public function testSettlesDeliveryWithRequestedOutcome(): void
    {
        $settlements = [];
        $delivery = $this->delivery(
            accept: static function () use (&$settlements): void {
                $settlements[] = 'accepted';
            },
            release: static function () use (&$settlements): void {
                $settlements[] = 'released';
            },
            reject: static function () use (&$settlements): void {
                $settlements[] = 'rejected';
            },
        );

        (new SettlementHelper())->settle($delivery, SettlementOutcome::Released);

        self::assertSame(['released'], $settlements);
    }

    /**
     * @param callable(int): void $accept
     * @param callable(int): void $release
     * @param callable(int): void $reject
     */
    private function delivery(callable $accept, callable $release, callable $reject): Delivery
    {
        $reflection = new \ReflectionClass(Delivery::class);
        $delivery = $reflection->newInstanceWithoutConstructor();

        $this->setProperty($delivery, 'deliveryId', 1);
        $this->setProperty($delivery, 'message', new Message('payload'));
        $this->setProperty($delivery, 'accept', $accept(...));
        $this->setProperty($delivery, 'release', $release(...));
        $this->setProperty($delivery, 'reject', $reject(...));

        return $delivery;
    }

    private function setProperty(Delivery $delivery, string $property, mixed $value): void
    {
        $reflectionProperty = new \ReflectionProperty($delivery, $property);
        $reflectionProperty->setValue($delivery, $value);
    }
}
