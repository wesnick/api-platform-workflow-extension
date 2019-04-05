<?php declare(strict_types=1);

namespace Wesnick\Workflow\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
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
        if (!$container->hasDefinition('workflow.registry')) {
            return;
        }

        // @TODO: add validator
//        $validator        = $container->getDefinition(SubjectValidatorListener::class);

        $managerArgs = [];
        $classMap = [];

        // Iterate over workflows and create services
        /** @var Definition $workflow */
        /** @var Definition $supportStrategy */
        foreach ($this->workflowGenerator($container) as [$workflow, $supportStrategy]) {
            // only support InstanceOfSupportStrategy for now
            if (InstanceOfSupportStrategy::class !== $supportStrategy->getClass()) {
                throw new \RuntimeException(sprintf('Wesnick Workflow Bundle requires use of InstanceOfSupportStrategy, workflow %s is using strategy %s', (string) $workflow, $supportStrategy->getClass()));
            }

            $className = $supportStrategy->getArgument(0);
            $workflowShortName = $workflow->getArgument(3);
            $currentWorkflowDefinition = $container->getDefinition($workflow->getArgument(0));
            $metadataStoreDef = $currentWorkflowDefinition->getArgument(3);

            $classMap[$className][] = $workflowShortName;

            $currentDef = $container
                ->register('workflow.api.configuration.'.$workflowShortName, WorkflowConfiguration::class)
                ->setArguments([
                    $workflowShortName,
                    $className,
                    $currentWorkflowDefinition,
                    $metadataStoreDef,
                ])
            ;

            $managerArgs[] = $currentDef;

            // @TODO: add validator
//                $validator->addTag(
//                    'kernel.event_listener', ['event' => 'workflow.'.$workflow.'.guard', 'method' => 'onGuard']
//                );
        }

        $container->setParameter('wesnick.workflow_extension.supported_resources', $classMap);
        $container->getDefinition(WorkflowManager::class)->setArgument('$workflowConfiguration', $managerArgs);
    }

    private function workflowGenerator(ContainerBuilder $container): \Generator
    {
        $registry = $container->getDefinition('workflow.registry');
        foreach ($registry->getMethodCalls() as $call) {
            [, [$workflowReference, $supportStrategy]] = $call;
            yield [$container->getDefinition($workflowReference), $supportStrategy];
        }
    }
}
