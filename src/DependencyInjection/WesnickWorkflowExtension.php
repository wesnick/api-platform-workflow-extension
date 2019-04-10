<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Wesnick\WorkflowBundle\Validation\ChainedWorkflowValidationStrategy;
use Wesnick\WorkflowBundle\Validation\ValidationStateProviderStrategy;
use Wesnick\WorkflowBundle\Validation\WorkflowValidationStrategy;

/**
 * {@inheritdoc}
 */
class WesnickWorkflowExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('workflow.xml');
        if (true === $config['api_patch_transitions']) {
            $loader->load('api_patch.xml');
        }

        if (true === $config['workflow_validation_guard']) {
            $loader->load('workflow_validation.xml');
            // @TODO: add a tag
            $chainedValidator = $container->getDefinition(ChainedWorkflowValidationStrategy::class);
            $chainedValidator->setArgument(0, [
                $container->getDefinition(WorkflowValidationStrategy::class),
                $container->getDefinition(ValidationStateProviderStrategy::class)
            ]);
        }
    }
}
