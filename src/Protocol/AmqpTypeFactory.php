<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Protocol;

use Sigbits\Amqp\Protocol\Type\AmqpArray;
use Sigbits\Amqp\Protocol\Type\AmqpDescribedValue;
use Sigbits\Amqp\Protocol\Type\AmqpList;
use Sigbits\Amqp\Protocol\Type\AmqpMap;
use Sigbits\Amqp\Protocol\Type\Binary;
use Sigbits\Amqp\Protocol\Type\Byte;
use Sigbits\Amqp\Protocol\Type\Char;
use Sigbits\Amqp\Protocol\Type\Decimal128;
use Sigbits\Amqp\Protocol\Type\Decimal32;
use Sigbits\Amqp\Protocol\Type\Decimal64;
use Sigbits\Amqp\Protocol\Type\Float32;
use Sigbits\Amqp\Protocol\Type\Float64;
use Sigbits\Amqp\Protocol\Type\Int_;
use Sigbits\Amqp\Protocol\Type\Long_;
use Sigbits\Amqp\Protocol\Type\Short;
use Sigbits\Amqp\Protocol\Type\Symbol;
use Sigbits\Amqp\Protocol\Type\Timestamp;
use Sigbits\Amqp\Protocol\Type\UByte;
use Sigbits\Amqp\Protocol\Type\UInt;
use Sigbits\Amqp\Protocol\Type\ULong;
use Sigbits\Amqp\Protocol\Type\UShort;
use Sigbits\Amqp\Protocol\Type\Uuid;

final readonly class AmqpTypeFactory implements AmqpTypeFactoryInterface
{
    public function binary(string $value): Binary
    {
        return new Binary($value);
    }

    public function symbol(string $value): Symbol
    {
        return new Symbol($value);
    }

    public function uuid(string $value): Uuid
    {
        return new Uuid($value);
    }

    public function timestamp(int $milliseconds): Timestamp
    {
        return new Timestamp($milliseconds);
    }

    public function byte(int $value): Byte
    {
        return new Byte($value);
    }

    public function short(int $value): Short
    {
        return new Short($value);
    }

    public function int(int $value): Int_
    {
        return new Int_($value);
    }

    public function long(int $value): Long_
    {
        return new Long_($value);
    }

    public function ubyte(int $value): UByte
    {
        return new UByte($value);
    }

    public function ushort(int $value): UShort
    {
        return new UShort($value);
    }

    public function uint(int $value): UInt
    {
        return new UInt($value);
    }

    public function ulong(int|string $value): ULong
    {
        return new ULong($value);
    }

    public function float32(float $value): Float32
    {
        return new Float32($value);
    }

    public function float64(float $value): Float64
    {
        return new Float64($value);
    }

    public function decimal32(string $bytes): Decimal32
    {
        return new Decimal32($bytes);
    }

    public function decimal64(string $bytes): Decimal64
    {
        return new Decimal64($bytes);
    }

    public function decimal128(string $bytes): Decimal128
    {
        return new Decimal128($bytes);
    }

    public function char(int $codePoint): Char
    {
        return new Char($codePoint);
    }

    public function list(array $items): AmqpList
    {
        return new AmqpList($items);
    }

    public function map(array $pairs): AmqpMap
    {
        return new AmqpMap($pairs);
    }

    public function amqpArray(string $elementConstructor, array $items): AmqpArray
    {
        return new AmqpArray($elementConstructor, $items);
    }

    public function described(mixed $descriptor, mixed $value): AmqpDescribedValue
    {
        return new AmqpDescribedValue($descriptor, $value);
    }
}
