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

interface AmqpTypeFactoryInterface
{
    public function binary(string $value): Binary;

    public function symbol(string $value): Symbol;

    public function uuid(string $value): Uuid;

    public function timestamp(int $milliseconds): Timestamp;

    public function byte(int $value): Byte;

    public function short(int $value): Short;

    public function int(int $value): Int_;

    public function long(int $value): Long_;

    public function ubyte(int $value): UByte;

    public function ushort(int $value): UShort;

    public function uint(int $value): UInt;

    public function ulong(int|string $value): ULong;

    public function float32(float $value): Float32;

    public function float64(float $value): Float64;

    public function decimal32(string $bytes): Decimal32;

    public function decimal64(string $bytes): Decimal64;

    public function decimal128(string $bytes): Decimal128;

    public function char(int $codePoint): Char;

    /**
     * @param array<array-key, mixed> $items
     */
    public function list(array $items): AmqpList;

    /**
     * @param array<array-key, mixed> $pairs
     */
    public function map(array $pairs): AmqpMap;

    /**
     * @param array<array-key, mixed> $items
     */
    public function amqpArray(string $elementConstructor, array $items): AmqpArray;

    public function described(mixed $descriptor, mixed $value): AmqpDescribedValue;
}
