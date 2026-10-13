<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Tests\Endpoint;

use PHPUnit\Framework\TestCase;
use Sigbits\AmqpBundle\DependencyInjection\SigbitsAmqpExtension;
use Sigbits\AmqpBundle\Endpoint\ReceiverEndpointInterface;
use Sigbits\AmqpBundle\Endpoint\SenderEndpointInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EndpointServiceTest extends TestCase
{
    public function testRegistersNamedSenderAndReceiverEndpoints(): void
    {
        $container = new ContainerBuilder();
        (new SigbitsAmqpExtension())->load([[
            'connections' => [
                'default' => [
                    'uri' => 'amqp://guest:guest@localhost:5672',
                ],
            ],
            'senders' => [
                'orders' => [
                    'address' => '/queues/orders',
                    'link_name' => 'orders-sender',
                    'handle' => 3,
                    'max_frame_size' => 4096,
                ],
            ],
            'receivers' => [
                'orders' => [
                    'address' => '/queues/orders',
                    'link_name' => 'orders-receiver',
                    'handle' => 4,
                    'credit' => 25,
                    'receive_timeout' => 1500,
                ],
            ],
        ]], $container);
        $container->compile();

        self::assertTrue($container->has('sigbits_amqp.sender_endpoint.orders'));
        self::assertTrue($container->has('sigbits_amqp.receiver_endpoint.orders'));
        self::assertInstanceOf(
            SenderEndpointInterface::class,
            $container->get('sigbits_amqp.sender_endpoint.orders'),
        );
        self::assertInstanceOf(
            ReceiverEndpointInterface::class,
            $container->get('sigbits_amqp.receiver_endpoint.orders'),
        );

        self::assertSame(
            'Sigbits\AmqpBundle\Endpoint\OpenSenderEndpoint',
            (string) (new \ReflectionMethod(SenderEndpointInterface::class, 'connect'))->getReturnType(),
        );
        self::assertSame(
            'Sigbits\AmqpBundle\Endpoint\OpenReceiverEndpoint',
            (string) (new \ReflectionMethod(ReceiverEndpointInterface::class, 'connect'))->getReturnType(),
        );
    }
}
