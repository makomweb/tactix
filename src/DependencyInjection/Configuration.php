<?php

declare(strict_types=1);

namespace Tactix\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('tactix');

        $rootNode = $treeBuilder->getRootNode();
        $rootNode->children()
            ->arrayNode('blacklist')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->scalarPrototype()->end()
                ->end()
                ->defaultValue([
                    'Entity' => ['Factory', 'Service', 'AggregateRoot'],
                    'ValueObject' => ['Entity', 'AggregateRoot', 'Repository', 'Factory', 'Service'],
                    'AggregateRoot' => ['Factory'],
                    'Repository' => ['Factory', 'Service'],
                    'Factory' => ['Repository'],
                    'Service' => [],
                ])
            ->end()
        ->end();

        return $treeBuilder;
    }
}
