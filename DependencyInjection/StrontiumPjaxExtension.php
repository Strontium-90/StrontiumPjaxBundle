<?php

namespace Strontium\PjaxBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * {@inheritdoc}
 */
class StrontiumPjaxExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container
            ->getDefinition('pjax.twig.extension')
            ->addMethodCall('setLayouts', [$config['layouts']])
            ->addMethodCall('setFrames', [$config['frames']])
            ->addMethodCall('setDefaultFrame', [$config['default_frame']]);

        if ($config['add_content_version'] === true && $config['version_generator']) {
            $container
                ->getDefinition('pjax.helper')
                ->addMethodCall('setVersionGenerator', [new Reference($config['version_generator'])]);

            $container
                ->getDefinition('pjax.kernel.event_listener.response')
                ->addTag('kernel.event_listener', [
                    'event'  => 'kernel.response',
                    'method' => 'addPjaxVersion'
                ]);
        }
        if ($config['menu']) {
            $loader->load('menu.yml');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $input = array(
            '@StrontiumPjaxBundle/Resources/public/js/pjax.js',
            '@StrontiumPjaxBundle/Resources/public/js/modal.js',
            '@StrontiumPjaxBundle/Resources/public/js/flash.js',
        );

        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);
        if ($config['menu']) {
            $input[] = '@StrontiumPjaxBundle/Resources/public/js/menu.js';
        }


        $container->prependExtensionConfig('assetic', array(
            'assets' => array(
                'pjax' => array(
                    'input' => $input,
                )
            )
        ));
    }
}
