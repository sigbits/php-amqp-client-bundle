<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\DependencyInjection;

use Sigbits\AmqpBundle\Connection\ConnectionFactory;
use Sigbits\AmqpBundle\Connection\ConnectionFactoryInterface;
use Sigbits\AmqpBundle\Health\ConnectionHealthChecker;
use Sigbits\AmqpBundle\Health\ConnectionHealthCheckerInterface;
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
        if (!preg_match('/^[A-Za-z0-9_.-]+$/', $name)) {
            throw new InvalidConfigurationException(sprintf(
                'The sigbits_amqp connection name "%s" is invalid. Use only letters, numbers, underscores, dots, and hyphens.',
                $name,
            ));
        }

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
}
