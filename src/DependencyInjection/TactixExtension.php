<?php

declare(strict_types=1);

namespace Tactix\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class TactixExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        /** @var array<string, array<string>> $blacklist */
        $blacklist = $config['blacklist'];
        $container->setParameter('tactix.blacklist', $blacklist);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../resources/config'));
        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'tactix';
    }
}
