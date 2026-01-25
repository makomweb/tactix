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
            ->scalarNode('some_option')->defaultNull()->end()
        ->end();

        return $treeBuilder;
    }
}
