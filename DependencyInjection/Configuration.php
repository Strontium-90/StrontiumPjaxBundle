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
                ->scalarNode('default_frame')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('frames')
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('layouts')
                    ->defaultValue([
                        'pjax'=>  'StrontiumPjaxBundle::pjax.html.twig',
                    ])
                    ->prototype('scalar')->end()
                    ->validate()
                        ->ifTrue(function($layouts){
                            return !isset($layouts['pjax']);
                        })
                        ->thenInvalid('Layout "pjax" must be configured')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
