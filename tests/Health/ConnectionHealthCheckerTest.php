<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\Health;

use PHPUnit\Framework\TestCase;
use Sigbits\Amqp\Client\Connection;
use Sigbits\Amqp\Engine\ConnectionEngine;
use Sigbits\AmqpBundle\Connection\ConnectionFactoryInterface;
use Sigbits\AmqpBundle\Health\ConnectionHealthChecker;

final class ConnectionHealthCheckerTest extends TestCase
{
    public function testReturnsHealthyResultAndClosesConnection(): void
    {
        $factory = new InMemoryConnectionFactory();
        $checker = new ConnectionHealthChecker($factory);

        $result = $checker->check();

        self::assertTrue($result->isHealthy());
        self::assertNull($result->errorMessage());
        self::assertNull($result->exceptionClass());
        self::assertNotNull($factory->stream);
        self::assertFalse(is_resource($factory->stream));
    }

    public function testReturnsUnhealthyResultWhenConnectionFails(): void
    {
        $checker = new ConnectionHealthChecker(new FailingConnectionFactory());

        $result = $checker->check();

        self::assertFalse($result->isHealthy());
        self::assertSame('broker unavailable', $result->errorMessage());
        self::assertSame(\RuntimeException::class, $result->exceptionClass());
    }
}

final class InMemoryConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @var resource|null
     */
    public mixed $stream = null;

    public function connect(): Connection
    {
        $this->stream = fopen('php://temp', 'r+');

        if ($this->stream === false) {
            throw new \RuntimeException('Unable to create in-memory stream.');
        }

        $connection = (new \ReflectionClass(Connection::class))->newInstanceWithoutConstructor();

        $streamProperty = new \ReflectionProperty($connection, 'stream');
        $streamProperty->setValue($connection, $this->stream);

        $engineProperty = new \ReflectionProperty($connection, 'engine');
        $engineProperty->setValue($connection, new ConnectionEngine('health-check-test', 'localhost'));

        return $connection;
    }
}

final class FailingConnectionFactory implements ConnectionFactoryInterface
{
    public function connect(): Connection
    {
        throw new \RuntimeException('broker unavailable');
    }
}
