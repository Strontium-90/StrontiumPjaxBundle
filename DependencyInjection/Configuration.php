<?php

namespace Strontium\PjaxBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link
 * http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
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
                        ->scalarNode('embedded')
                            ->defaultValue('StrontiumPjaxBundle::embedded.html.twig')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
