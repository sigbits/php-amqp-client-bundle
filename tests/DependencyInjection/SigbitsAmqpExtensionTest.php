<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sigbits\AmqpBundle\Connection\ConnectionFactoryInterface;
use Sigbits\AmqpBundle\DependencyInjection\SigbitsAmqpExtension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SigbitsAmqpExtensionTest extends TestCase
{
    public function testRegistersDefaultConnectionFactoryAlias(): void
    {
        $container = $this->loadContainer([
            'connections' => [
                'default' => [
                    'uri' => 'amqp://guest:guest@localhost:5672',
                ],
            ],
        ]);

        self::assertTrue($container->has('sigbits_amqp.connection_factory.default'));
        self::assertTrue($container->has(ConnectionFactoryInterface::class));
        self::assertInstanceOf(
            ConnectionFactoryInterface::class,
            $container->get('sigbits_amqp.connection_factory.default'),
        );
        self::assertSame(
            $container->get('sigbits_amqp.connection_factory.default'),
            $container->get(ConnectionFactoryInterface::class),
        );
    }

    public function testRegistersNamedConnectionFactoriesAndDefaultAlias(): void
    {
        $container = $this->loadContainer([
            'default_connection' => 'analytics',
            'connections' => [
                'default' => [
                    'uri' => 'amqp://guest:guest@localhost:5672',
                ],
                'analytics' => [
                    'uri' => 'amqps://analytics:secret@broker.example.com',
                    'container_id' => 'analytics-worker',
                    'timeout' => 5.5,
                    'tls' => [
                        'peer_name' => 'broker.example.com',
                        'cafile' => '/etc/ssl/certs/broker-ca.pem',
                    ],
                    'sasl' => [
                        'mechanism' => 'plain',
                        'username' => 'analytics',
                        'password' => 'secret',
                    ],
                ],
            ],
        ]);

        self::assertTrue($container->has('sigbits_amqp.connection_factory.default'));
        self::assertTrue($container->has('sigbits_amqp.connection_factory.analytics'));
        self::assertSame(
            $container->get('sigbits_amqp.connection_factory.analytics'),
            $container->get(ConnectionFactoryInterface::class),
        );
    }

    public function testRejectsUnknownDefaultConnection(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('default_connection');

        $this->loadContainer([
            'default_connection' => 'missing',
            'connections' => [
                'default' => [
                    'uri' => 'amqp://guest:guest@localhost:5672',
                ],
            ],
        ]);
    }

    public function testRejectsNonPositiveTimeout(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('timeout');

        $this->loadContainer([
            'connections' => [
                'default' => [
                    'uri' => 'amqp://guest:guest@localhost:5672',
                    'timeout' => 0,
                ],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function loadContainer(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $extension = new SigbitsAmqpExtension();
        $extension->load([$config], $container);
        $container->compile();

        return $container;
    }
}
