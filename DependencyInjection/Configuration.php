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
            ->append(
                (new NodeBuilder())
                    ->arrayNode('layouts')
                    //->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
            )
            ->append(
                (new NodeBuilder())
                    ->booleanNode('add_version')
                    ->defaultValue(false)
            )
            ->append(
                (new NodeBuilder())
                    ->scalarNode('version_generator')
                    ->defaultValue('pjax.version_generator.auth_token')
            )
            ->end();

        return $treeBuilder;
    }
}
