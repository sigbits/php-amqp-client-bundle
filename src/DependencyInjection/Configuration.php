<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sigbits_amqp');
        $rootNode = $this->rootNode($treeBuilder);

        $rootChildren = $rootNode->children();

        $rootChildren
            ->scalarNode('default_connection')
            ->defaultValue('default')
            ->cannotBeEmpty();

        $connectionsNode = $rootChildren
            ->arrayNode('connections')
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name');

        $connectionPrototype = $connectionsNode->arrayPrototype();
        $connectionChildren = $connectionPrototype->children();

        $connectionChildren
            ->scalarNode('uri')
            ->isRequired()
            ->cannotBeEmpty();

        $connectionChildren
            ->scalarNode('container_id')
            ->defaultValue('sigbits-php-amqp-client')
            ->cannotBeEmpty();

        $connectionChildren
            ->floatNode('timeout')
            ->defaultValue(30.0)
            ->validate()
                ->ifTrue(static fn (float $value): bool => $value <= 0.0)
                ->thenInvalid('The timeout must be greater than 0.');

        $tlsNode = $connectionChildren->arrayNode('tls');
        $tlsChildren = $tlsNode->children();
        $tlsChildren->booleanNode('verify_peer')->defaultTrue();
        $tlsChildren->booleanNode('verify_peer_name')->defaultTrue();
        $tlsChildren->scalarNode('peer_name')->defaultNull();
        $tlsChildren->scalarNode('cafile')->defaultNull();
        $tlsChildren->scalarNode('local_cert')->defaultNull();

        $saslNode = $connectionChildren
            ->arrayNode('sasl')
            ->addDefaultsIfNotSet();
        $saslNode
            ->validate()
                ->ifTrue(static fn (array $value): bool => $value['mechanism'] === 'plain' && $value['username'] === '')
                ->thenInvalid('The sasl.username option is required when sasl.mechanism is "plain".');

        $saslChildren = $saslNode->children();
        $saslChildren
            ->enumNode('mechanism')
            ->values(['auto', 'anonymous', 'plain'])
            ->defaultValue('auto');
        $saslChildren->scalarNode('username')->defaultValue('');
        $saslChildren->scalarNode('password')->defaultValue('');
        $saslChildren->scalarNode('authorization_id')->defaultValue('');

        return $treeBuilder;
    }

    private function rootNode(TreeBuilder $treeBuilder): ArrayNodeDefinition
    {
        return $this->arrayNode($treeBuilder->getRootNode());
    }

    private function arrayNode(object $node): ArrayNodeDefinition
    {
        if (!$node instanceof ArrayNodeDefinition) {
            throw new \LogicException('The sigbits_amqp configuration root node must be an array node.');
        }

        return $node;
    }
}
