<?php declare(strict_types=1);

namespace Wesnick\Workflow\DependencyInjection\Compiler;

use Wesnick\Workflow\Configuration\WorkflowConfiguration;
use Wesnick\Workflow\EventListener\SubjectValidatorListener;
use Wesnick\Workflow\WorkflowManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class WorkflowPass.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {

        $directories = $container->getParameter('api_platform.resource_class_directories');
        $directories[] = realpath(__DIR__.'/../../Model');
        $container->setParameter('api_platform.resource_class_directories', $directories);

        if (!$container->hasDefinition('workflow.registry')) {
            return;
        }

        $registry    = $container->getDefinition('workflow.registry');

        $workflowMap = array_map(function ($call) use ($container) {
            [, [$workflow, $supportStrategy]] = $call;

            $className = $supportStrategy->getArguments()[0];
            $workflowDef = $container->getDefinition($workflow);
            $workflowShortName = $workflowDef->getArgument(3);
            $currentWorkflowDefinition = $container->getDefinition($workflowDef->getArgument(0));
            $metadataStoreDef = $currentWorkflowDefinition->getArgument(3);

            return [$workflowShortName, $className, $workflow, $metadataStoreDef];
        }, $registry->getMethodCalls());

        // @TODO: add validator
//        $validator        = $container->getDefinition(SubjectValidatorListener::class);
        $managerArguments = [];
        foreach ($workflowMap as [$workflowShortName, $className, $workflow, $metadataStoreDef]) {
            if (!in_array($workflowShortName, $arguments[$className] ?? [], true)) {
                $currentDef = $container
                    ->register('workflow.api.configuration.'.(string) $workflow, WorkflowConfiguration::class)
                    ->setArguments([
                        $workflowShortName,
                        $className,
                        $container->getDefinition($workflow.'.definition'),
                        $metadataStoreDef,
                    ]);
                $managerArguments[] = $currentDef;
                // @TODO: add validator
//                $validator->addTag(
//                    'kernel.event_listener', ['event' => 'workflow.'.$workflow.'.guard', 'method' => 'onGuard']
//                );
            }
        }

        $container->getDefinition(WorkflowManager::class)->setArgument('$workflowConfiguration', $managerArguments);
    }
}
