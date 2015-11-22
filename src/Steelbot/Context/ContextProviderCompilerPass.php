<?php

namespace Steelbot\Context;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ContextProviderCompilerPass
 * @package Steelbot\Context
 */
class ContextProviderCompilerPass implements CompilerPassInterface
{
    /**
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('context_router')) {
            return;
        }

        $definition = $container->findDefinition('context_router');

        $contextProviders = $container->findTaggedServiceIds('steelbot.context.provider');
        foreach ($contextProviders as $id => $tags) {
            $definition->addMethodCall('addContextProvider', [new Reference($id)]);
        }
    }
}