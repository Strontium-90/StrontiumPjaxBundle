<?php

namespace Strontium\PjaxBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * {@inheritdoc}
 */
class StrontiumPjaxExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if ($config['add_version'] === true && $config['version_generator']) {
            $container
                ->getDefinition('pjax')
                ->addMethodCall('setVersionGenerator', [new Reference($config['version_generator'])]);

            $container
                ->getDefinition('pjax.kernel.event_listener.response')
                ->addTag('kernel.event_listener', [
                    'event'  => 'kernel.response',
                    'method' => 'addPjaxVersion'
                ]);
        }
    }
}
