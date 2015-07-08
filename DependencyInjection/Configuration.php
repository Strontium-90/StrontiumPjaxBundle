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
                ->scalarNode('version_generator')->defaultValue('pjax.version_generator.auth_token')->end()
                ->scalarNode('default_layout')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('layouts')
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('frames')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('base')
                            ->defaultValue('StrontiumPjaxBundle::base.html.twig')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('pjax')
                            ->defaultValue('StrontiumPjaxBundle::pjax.html.twig')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
