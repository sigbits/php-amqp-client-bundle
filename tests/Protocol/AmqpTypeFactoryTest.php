<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\Protocol;

use PHPUnit\Framework\TestCase;
use Sigbits\Amqp\Protocol\Type\AmqpDescribedValue;
use Sigbits\Amqp\Protocol\Type\AmqpList;
use Sigbits\Amqp\Protocol\Type\AmqpMap;
use Sigbits\Amqp\Protocol\Type\Symbol;
use Sigbits\Amqp\Protocol\Type\ULong;
use Sigbits\AmqpBundle\Protocol\AmqpTypeFactory;

final class AmqpTypeFactoryTest extends TestCase
{
    public function testCreatesClient13GenericContainerValues(): void
    {
        $factory = new AmqpTypeFactory();

        $map = $factory->map([
            [$factory->symbol('key'), 'value'],
        ]);
        $list = $factory->list(['a', 1, true]);
        $array = $factory->amqpArray('symbol', [$factory->symbol('one')]);
        $described = $factory->described($factory->ulong('42'), $factory->symbol('value'));

        self::assertInstanceOf(AmqpMap::class, $map);
        self::assertInstanceOf(AmqpList::class, $list);
        self::assertSame('symbol', $array->elementConstructor);
        self::assertInstanceOf(AmqpDescribedValue::class, $described);
        self::assertInstanceOf(Symbol::class, $described->value);
        self::assertInstanceOf(ULong::class, $described->descriptor);
    }
}
