<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sigbits\AmqpBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testAppliesDefaultsForSingleConnection(): void
    {
        $config = $this->process([
            'connections' => [
                'default' => [
                    'uri' => 'amqp://guest:guest@localhost:5672',
                ],
            ],
        ]);

        self::assertSame('default', $config['default_connection']);
        self::assertSame('amqp://guest:guest@localhost:5672', $config['connections']['default']['uri']);
        self::assertSame('sigbits-php-amqp-client', $config['connections']['default']['container_id']);
        self::assertSame(30.0, $config['connections']['default']['timeout']);
        self::assertSame(
            [
                'mechanism' => 'auto',
                'username' => '',
                'password' => '',
                'authorization_id' => '',
            ],
            $config['connections']['default']['sasl'],
        );
        self::assertArrayNotHasKey('tls', $config['connections']['default']);
        self::assertSame([], $config['senders']);
        self::assertSame([], $config['receivers']);
    }

    public function testAppliesDefaultsForSenderAndReceiverEndpoints(): void
    {
        $config = $this->process([
            'connections' => [
                'default' => [
                    'uri' => 'amqp://guest:guest@localhost:5672',
                ],
            ],
            'senders' => [
                'orders' => [
                    'address' => '/queues/orders',
                ],
            ],
            'receivers' => [
                'orders' => [
                    'address' => '/queues/orders',
                ],
            ],
        ]);

        self::assertSame(
            [
                'address' => '/queues/orders',
                'connection' => 'default',
                'link_name' => 'sender',
                'handle' => 0,
                'max_frame_size' => 512,
            ],
            $config['senders']['orders'],
        );
        self::assertSame(
            [
                'address' => '/queues/orders',
                'connection' => 'default',
                'link_name' => 'receiver',
                'handle' => 1,
                'credit' => 1,
                'receive_timeout' => 1000,
            ],
            $config['receivers']['orders'],
        );
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function process(array $config): array
    {
        return (new Processor())->processConfiguration(new Configuration(), [$config]);
    }
}
