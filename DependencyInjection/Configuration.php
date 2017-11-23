<?php

namespace Strontium\PjaxBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * {@inheritdoc}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('strontium_pjax');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('add_content_version')->defaultValue(false)->end()
                ->booleanNode('menu')->defaultValue(false)->end()
                ->scalarNode('version_generator')->defaultValue('pjax.version_generator.auth_token')->end()
                ->arrayNode('sections')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->arrayNode('layouts')
                                ->requiresAtLeastOneElement()
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('default_layout')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('base_template')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('pjax_template')
                                ->isRequired()
                                ->defaultValue('StrontiumPjaxBundle::pjax.html.twig')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
