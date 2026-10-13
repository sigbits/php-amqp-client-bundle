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

        $sendersNode = $rootChildren
            ->arrayNode('senders')
            ->defaultValue([])
            ->useAttributeAsKey('name');
        $senderPrototype = $sendersNode->arrayPrototype();
        $senderChildren = $senderPrototype->children();
        $senderChildren
            ->scalarNode('connection')
            ->defaultValue('default')
            ->cannotBeEmpty();
        $senderChildren
            ->scalarNode('address')
            ->isRequired()
            ->cannotBeEmpty();
        $senderChildren
            ->scalarNode('link_name')
            ->defaultValue('sender')
            ->cannotBeEmpty();
        $senderChildren
            ->integerNode('handle')
            ->defaultValue(0)
            ->validate()
                ->ifTrue(static fn (int $value): bool => $value < 0)
                ->thenInvalid('The sender handle must be greater than or equal to 0.');
        $senderChildren
            ->integerNode('max_frame_size')
            ->defaultValue(512)
            ->validate()
                ->ifTrue(static fn (int $value): bool => $value <= 0)
                ->thenInvalid('The sender max_frame_size must be greater than 0.');

        $receiversNode = $rootChildren
            ->arrayNode('receivers')
            ->defaultValue([])
            ->useAttributeAsKey('name');
        $receiverPrototype = $receiversNode->arrayPrototype();
        $receiverChildren = $receiverPrototype->children();
        $receiverChildren
            ->scalarNode('connection')
            ->defaultValue('default')
            ->cannotBeEmpty();
        $receiverChildren
            ->scalarNode('address')
            ->isRequired()
            ->cannotBeEmpty();
        $receiverChildren
            ->scalarNode('link_name')
            ->defaultValue('receiver')
            ->cannotBeEmpty();
        $receiverChildren
            ->integerNode('handle')
            ->defaultValue(1)
            ->validate()
                ->ifTrue(static fn (int $value): bool => $value < 0)
                ->thenInvalid('The receiver handle must be greater than or equal to 0.');
        $receiverChildren
            ->integerNode('credit')
            ->defaultValue(1)
            ->validate()
                ->ifTrue(static fn (int $value): bool => $value < 0)
                ->thenInvalid('The receiver credit must be greater than or equal to 0.');
        $receiverChildren
            ->integerNode('receive_timeout')
            ->defaultValue(1000)
            ->validate()
                ->ifTrue(static fn (int $value): bool => $value < 0)
                ->thenInvalid('The receiver receive_timeout must be greater than or equal to 0.');

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
