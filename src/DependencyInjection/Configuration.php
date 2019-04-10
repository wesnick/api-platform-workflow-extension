<?php declare(strict_types=1);

namespace Wesnick\WorkflowBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('wesnick_workflow');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('api_patch_transitions')->defaultTrue()->end()
                ->booleanNode('workflow_validation_guard')->defaultTrue()->end()
            ->end()
        ;

        return $treeBuilder;
    }

}
