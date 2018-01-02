<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\SearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('blast_search');

        $rootNode
            ->children()
                ->arrayNode('elastic_search')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('hostname')
                            ->defaultValue('localhost')
                        ->end()
                        ->scalarNode('port')
                            ->defaultValue(9200)
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('global_index_name')
                    ->defaultValue('blast_search')
                ->end()
                ->scalarNode('default_result_template')
                    ->defaultValue('BlastSearchBundle:Search/Results:result.html.twig')
                ->end()
                ->arrayNode('templates')
                    ->useAttributeAsKey('class')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('class')->end()
                            ->scalarNode('template')->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('results_per_page')
                    ->defaultValue(20)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
