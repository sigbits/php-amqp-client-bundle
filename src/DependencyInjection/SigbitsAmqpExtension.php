<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\DependencyInjection;

use Sigbits\AmqpBundle\Connection\ConnectionFactory;
use Sigbits\AmqpBundle\Connection\ConnectionFactoryInterface;
use Sigbits\AmqpBundle\Endpoint\ReceiverEndpoint;
use Sigbits\AmqpBundle\Endpoint\SenderEndpoint;
use Sigbits\AmqpBundle\Health\ConnectionHealthChecker;
use Sigbits\AmqpBundle\Health\ConnectionHealthCheckerInterface;
use Sigbits\AmqpBundle\Message\MessageFactory;
use Sigbits\AmqpBundle\Message\MessageFactoryInterface;
use Sigbits\AmqpBundle\Protocol\AmqpTypeFactory;
use Sigbits\AmqpBundle\Protocol\AmqpTypeFactoryInterface;
use Sigbits\AmqpBundle\Protocol\TerminusFactory;
use Sigbits\AmqpBundle\Protocol\TerminusFactoryInterface;
use Sigbits\AmqpBundle\Settlement\SettlementHelper;
use Sigbits\AmqpBundle\Settlement\SettlementHelperInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class SigbitsAmqpExtension extends Extension
{
    /**
     * @param array<array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $connections = $config['connections'];
        $defaultConnection = $config['default_connection'];

        if (!isset($connections[$defaultConnection])) {
            throw new InvalidConfigurationException(sprintf(
                'The sigbits_amqp.default_connection "%s" does not reference a configured connection.',
                $defaultConnection,
            ));
        }

        foreach ($connections as $name => $connectionConfig) {
            $this->registerConnectionFactory($container, $name, $connectionConfig);
            $this->registerConnectionHealthChecker($container, $name);
        }

        foreach ($config['senders'] as $name => $senderConfig) {
            $this->registerSenderEndpoint($container, $name, $senderConfig, $connections);
        }

        foreach ($config['receivers'] as $name => $receiverConfig) {
            $this->registerReceiverEndpoint($container, $name, $receiverConfig, $connections);
        }

        $this->registerClient13Factories($container);

        $defaultServiceId = $this->connectionFactoryServiceId($defaultConnection);
        $defaultHealthCheckerServiceId = $this->connectionHealthCheckerServiceId($defaultConnection);

        $container
            ->setAlias(ConnectionFactoryInterface::class, $defaultServiceId)
            ->setPublic(true);
        $container
            ->setAlias('sigbits_amqp.connection_factory', $defaultServiceId)
            ->setPublic(true);
        $container
            ->setAlias(ConnectionHealthCheckerInterface::class, $defaultHealthCheckerServiceId)
            ->setPublic(true);
        $container
            ->setAlias('sigbits_amqp.connection_health_checker', $defaultHealthCheckerServiceId)
            ->setPublic(true);
    }

    /**
     * @param array{
     *     connection: string,
     *     address: string,
     *     link_name: string,
     *     handle: int,
     *     max_frame_size: int
     * } $senderConfig
     * @param array<string, mixed> $connections
     */
    private function registerSenderEndpoint(
        ContainerBuilder $container,
        string $name,
        array $senderConfig,
        array $connections,
    ): void {
        $this->assertServiceName($name, 'sender endpoint');
        $this->assertConfiguredConnection($senderConfig['connection'], $connections, 'sender endpoint', $name);

        $definition = new Definition(SenderEndpoint::class);
        $definition->setArguments([
            new Reference($this->connectionFactoryServiceId($senderConfig['connection'])),
            $senderConfig['address'],
            $senderConfig['link_name'],
            $senderConfig['handle'],
            $senderConfig['max_frame_size'],
        ]);
        $definition->setPublic(true);

        $container->setDefinition($this->senderEndpointServiceId($name), $definition);
    }

    /**
     * @param array{
     *     connection: string,
     *     address: string,
     *     link_name: string,
     *     handle: int,
     *     credit: int,
     *     receive_timeout: int
     * } $receiverConfig
     * @param array<string, mixed> $connections
     */
    private function registerReceiverEndpoint(
        ContainerBuilder $container,
        string $name,
        array $receiverConfig,
        array $connections,
    ): void {
        $this->assertServiceName($name, 'receiver endpoint');
        $this->assertConfiguredConnection($receiverConfig['connection'], $connections, 'receiver endpoint', $name);

        $definition = new Definition(ReceiverEndpoint::class);
        $definition->setArguments([
            new Reference($this->connectionFactoryServiceId($receiverConfig['connection'])),
            $receiverConfig['address'],
            $receiverConfig['link_name'],
            $receiverConfig['handle'],
            $receiverConfig['credit'],
            $receiverConfig['receive_timeout'],
        ]);
        $definition->setPublic(true);

        $container->setDefinition($this->receiverEndpointServiceId($name), $definition);
    }

    private function registerClient13Factories(ContainerBuilder $container): void
    {
        $container->setDefinition(MessageFactory::class, (new Definition(MessageFactory::class))->setPublic(true));
        $container->setAlias(MessageFactoryInterface::class, MessageFactory::class)->setPublic(true);
        $container->setAlias('sigbits_amqp.message_factory', MessageFactory::class)->setPublic(true);

        $container->setDefinition(AmqpTypeFactory::class, (new Definition(AmqpTypeFactory::class))->setPublic(true));
        $container->setAlias(AmqpTypeFactoryInterface::class, AmqpTypeFactory::class)->setPublic(true);
        $container->setAlias('sigbits_amqp.amqp_type_factory', AmqpTypeFactory::class)->setPublic(true);

        $container->setDefinition(TerminusFactory::class, (new Definition(TerminusFactory::class))->setPublic(true));
        $container->setAlias(TerminusFactoryInterface::class, TerminusFactory::class)->setPublic(true);
        $container->setAlias('sigbits_amqp.terminus_factory', TerminusFactory::class)->setPublic(true);

        $container->setDefinition(SettlementHelper::class, (new Definition(SettlementHelper::class))->setPublic(true));
        $container->setAlias(SettlementHelperInterface::class, SettlementHelper::class)->setPublic(true);
        $container->setAlias('sigbits_amqp.settlement_helper', SettlementHelper::class)->setPublic(true);
    }

    /**
     * @param array{
     *     uri: string,
     *     container_id: string,
     *     timeout: float,
     *     tls: array{
     *         verify_peer: bool,
     *         verify_peer_name: bool,
     *         peer_name: string|null,
     *         cafile: string|null,
     *         local_cert: string|null
     *     }|null,
     *     sasl: array{
     *         mechanism: 'auto'|'anonymous'|'plain',
     *         username: string,
     *         password: string,
     *         authorization_id: string
     *     }
     * } $connectionConfig
     */
    private function registerConnectionFactory(
        ContainerBuilder $container,
        string $name,
        array $connectionConfig,
    ): void {
        $this->assertServiceName($name, 'connection');

        $definition = new Definition(ConnectionFactory::class);
        $definition->setArguments([
            $connectionConfig['uri'],
            $connectionConfig['container_id'],
            $connectionConfig['timeout'],
            $connectionConfig['tls'] ?? null,
            $connectionConfig['sasl'],
        ]);
        $definition->setPublic(true);

        $container->setDefinition($this->connectionFactoryServiceId($name), $definition);
    }

    private function registerConnectionHealthChecker(ContainerBuilder $container, string $name): void
    {
        $definition = new Definition(ConnectionHealthChecker::class);
        $definition->setArguments([
            new Reference($this->connectionFactoryServiceId($name)),
        ]);
        $definition->setPublic(true);

        $container->setDefinition($this->connectionHealthCheckerServiceId($name), $definition);
    }

    private function connectionFactoryServiceId(string $name): string
    {
        return sprintf('sigbits_amqp.connection_factory.%s', $name);
    }

    private function connectionHealthCheckerServiceId(string $name): string
    {
        return sprintf('sigbits_amqp.connection_health_checker.%s', $name);
    }

    private function senderEndpointServiceId(string $name): string
    {
        return sprintf('sigbits_amqp.sender_endpoint.%s', $name);
    }

    private function receiverEndpointServiceId(string $name): string
    {
        return sprintf('sigbits_amqp.receiver_endpoint.%s', $name);
    }

    private function assertServiceName(string $name, string $kind): void
    {
        if (!preg_match('/^[A-Za-z0-9_.-]+$/', $name)) {
            throw new InvalidConfigurationException(sprintf(
                'The sigbits_amqp %s name "%s" is invalid. Use only letters, numbers, underscores, dots, and hyphens.',
                $kind,
                $name,
            ));
        }
    }

    /**
     * @param array<string, mixed> $connections
     */
    private function assertConfiguredConnection(
        string $connection,
        array $connections,
        string $kind,
        string $name,
    ): void {
        if (!isset($connections[$connection])) {
            throw new InvalidConfigurationException(sprintf(
                'The sigbits_amqp %s "%s" references unknown connection "%s".',
                $kind,
                $name,
                $connection,
            ));
        }
    }
}
