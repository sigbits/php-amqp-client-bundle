<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Sigbits\AmqpBundle\Connection\ConnectionFactory;
use Sigbits\AmqpBundle\Tests\Fixtures\DefaultConnectionFactoryConsumer;
use Sigbits\AmqpBundle\Tests\Fixtures\SigbitsAmqpTestingKernel;

final class BundleKernelTest extends TestCase
{
    public function testBundleRegistersConfiguredFactoryInSymfonyKernel(): void
    {
        $kernel = new SigbitsAmqpTestingKernel([
            'connections' => [
                'default' => [
                    'uri' => 'amqp://guest:guest@localhost:5672',
                ],
            ],
        ]);

        $kernel->boot();

        try {
            $container = $kernel->getContainer();

            self::assertTrue($container->has('sigbits_amqp.connection_factory.default'));
            self::assertInstanceOf(
                ConnectionFactory::class,
                $container->get('sigbits_amqp.connection_factory.default'),
            );
        } finally {
            $kernel->shutdown();
        }
    }

    public function testBundleAutowiresDefaultConnectionFactoryInSymfonyKernel(): void
    {
        $kernel = new SigbitsAmqpTestingKernel([
            'connections' => [
                'default' => [
                    'uri' => 'amqp://guest:guest@localhost:5672',
                ],
            ],
        ]);

        $kernel->boot();

        try {
            $container = $kernel->getContainer();
            $consumer = $container->get(DefaultConnectionFactoryConsumer::class);

            self::assertInstanceOf(DefaultConnectionFactoryConsumer::class, $consumer);
            self::assertSame(
                $container->get('sigbits_amqp.connection_factory.default'),
                $consumer->connectionFactory(),
            );
        } finally {
            $kernel->shutdown();
        }
    }

    public function testBundleResolvesConnectionUriFromEnvironmentVariable(): void
    {
        $_SERVER['AMQP_TEST_URL'] = 'amqps://docs:secret@broker.example.com:5671';

        $kernel = new SigbitsAmqpTestingKernel([
            'connections' => [
                'default' => [
                    'uri' => '%env(AMQP_TEST_URL)%',
                ],
            ],
        ]);

        $kernel->boot();

        try {
            $factory = $kernel->getContainer()->get('sigbits_amqp.connection_factory.default');

            self::assertInstanceOf(ConnectionFactory::class, $factory);
            self::assertSame(
                'amqps://docs:secret@broker.example.com:5671',
                $this->readFactoryUri($factory),
            );
        } finally {
            $kernel->shutdown();
            unset($_SERVER['AMQP_TEST_URL']);
        }
    }

    private function readFactoryUri(ConnectionFactory $factory): string
    {
        $property = new \ReflectionProperty($factory, 'uri');
        $value = $property->getValue($factory);

        self::assertIsString($value);

        return $value;
    }
}
