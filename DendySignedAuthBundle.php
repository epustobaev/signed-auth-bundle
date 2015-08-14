<?php

namespace Dendy\SignedAuthBundle;

use Dendy\SignedAuthBundle\DependencyInjection\Security\Factory\SignedSecurityFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SignedAuthBundle
 *
 * @package Dendy\SignedAuthBundle
 */
class DendySignedAuthBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new SignedSecurityFactory());
    }
}
