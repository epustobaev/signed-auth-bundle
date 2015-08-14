<?php
/**
 * @copyright 2015
 * @author    Edward Pustobaev <eduardpustobaev@gmail.com>
 */

namespace Dendy\Bundle\SignedAuthBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SignedSecurityFactory
 *
 * @package Dendy\Bundle\SignedAuthBundle\DependencyInjection\Security\Factory
 */
class SignedSecurityFactory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.signed.'.$id;
        $container->setDefinition($providerId, new DefinitionDecorator('signed.security.authentication.provider'))
                  ->replaceArgument(1, new Reference($userProvider))
                  ->replaceArgument(2, $config);

        $listenerId = 'security.authentication.listener.signed.'.$id;
        /*$listener   = */
        $container->setDefinition(
            $listenerId,
            new DefinitionDecorator('signed.security.authentication.listener')
        )->replaceArgument(2, $config);

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    /**
     * Defines the position at which the provider is called.
     * Possible values: pre_auth, form, http, and remember_me.
     *
     * @return string
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'signed';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
        /** @var ArrayNodeDefinition $node */
        $node
            ->children()
                ->enumNode('auth_type')->values(['header', 'request'])->defaultValue('request')->end()
                ->scalarNode('request_key')->defaultValue('sign')->end()
                ->scalarNode('token_delimiter')->defaultValue(':')->end()
                ->scalarNode('data_delimiter')->defaultValue('')->end()
                ->scalarNode('hash_alg')->defaultValue('md5')->end()
                ->arrayNode('signed_params')
                    ->children()
                        ->arrayNode('headers')->prototype('scalar')->end()->end()
                        ->arrayNode('query')->prototype('scalar')->end()->end()
                        ->arrayNode('request')->prototype('scalar')->end()->end()
                    ->end()
                ->end()
            ->end();
    }
}
