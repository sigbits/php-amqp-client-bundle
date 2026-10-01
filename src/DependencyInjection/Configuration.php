<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sigbits_amqp');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('default_connection')
                    ->defaultValue('default')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('connections')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('uri')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('container_id')
                                ->defaultValue('sigbits-php-amqp-client')
                                ->cannotBeEmpty()
                            ->end()
                            ->floatNode('timeout')
                                ->defaultValue(30.0)
                                ->validate()
                                    ->ifTrue(static fn (float $value): bool => $value <= 0.0)
                                    ->thenInvalid('The timeout must be greater than 0.')
                                ->end()
                            ->end()
                            ->arrayNode('tls')
                                ->children()
                                    ->booleanNode('verify_peer')->defaultTrue()->end()
                                    ->booleanNode('verify_peer_name')->defaultTrue()->end()
                                    ->scalarNode('peer_name')->defaultNull()->end()
                                    ->scalarNode('cafile')->defaultNull()->end()
                                    ->scalarNode('local_cert')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->arrayNode('sasl')
                                ->addDefaultsIfNotSet()
                                ->validate()
                                    ->ifTrue(static fn (array $value): bool => $value['mechanism'] === 'plain' && $value['username'] === '')
                                    ->thenInvalid('The sasl.username option is required when sasl.mechanism is "plain".')
                                ->end()
                                ->children()
                                    ->enumNode('mechanism')
                                        ->values(['auto', 'anonymous', 'plain'])
                                        ->defaultValue('auto')
                                    ->end()
                                    ->scalarNode('username')->defaultValue('')->end()
                                    ->scalarNode('password')->defaultValue('')->end()
                                    ->scalarNode('authorization_id')->defaultValue('')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
