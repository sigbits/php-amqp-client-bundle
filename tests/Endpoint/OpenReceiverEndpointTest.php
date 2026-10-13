<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\Endpoint;

use PHPUnit\Framework\TestCase;
use Sigbits\AmqpBundle\Endpoint\OpenReceiverEndpoint;

final class OpenReceiverEndpointTest extends TestCase
{
    public function testExposesDeliveryIteratorWithNullableTimeoutOverride(): void
    {
        $method = new \ReflectionMethod(OpenReceiverEndpoint::class, 'deliveries');
        $parameters = $method->getParameters();

        self::assertSame('iterable', (string) $method->getReturnType());
        self::assertCount(1, $parameters);
        self::assertSame('timeoutMilliseconds', $parameters[0]->getName());
        self::assertTrue($parameters[0]->allowsNull());
        self::assertTrue($parameters[0]->isDefaultValueAvailable());
        self::assertNull($parameters[0]->getDefaultValue());
    }
}
