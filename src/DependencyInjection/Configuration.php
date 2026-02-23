<?php

declare(strict_types=1);

namespace Tactix\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Tactix\Blacklist;

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
                ->defaultValue(Blacklist::DEFAULT)
            ->end()
        ->end();

        return $treeBuilder;
    }
}
