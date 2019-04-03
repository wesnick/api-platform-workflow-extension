<?php declare(strict_types=1);

/*
 * Copyright (c) 2019, Wesley O. Nichols
 */

namespace Wesnick\Workflow;

use ApiPlatform\Core\Api\OperationType;
use ApiPlatform\Core\Bridge\Symfony\Routing\RouteNameGenerator;
use Wesnick\Workflow\Configuration\WorkflowConfiguration;
use Wesnick\Workflow\Model\Action;
use Wesnick\Workflow\Model\EntryPoint;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;

/**
 * The workflow manager should be called from controllers to perform workflow transitions.  A controller should first
 * check the transitions available.  This will include both "enabled" transitions, ie, transitions that can be performed
 * without additional modification to the subject, as well as disabled transitions, transitions that require
 * modifications to the subject, or some external data source, in order to be performed.  Unavailable transitions are not
 * included by default. All mutations that should be applied during the workflow should occur in workflow listeners before
 * the transition occurs.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowManager
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var WorkflowConfiguration[]
     */
    private $workflowConfiguration;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccess;

    /**
     * WorkflowManager constructor.
     *
     * @param Registry                  $registry
     * @param RouterInterface           $router
     * @param array                     $workflowConfiguration
     * @param PropertyAccessorInterface $propertyAccess
     */
    public function __construct(Registry $registry, RouterInterface $router, array $workflowConfiguration, PropertyAccessorInterface $propertyAccess)
    {
        $this->registry              = $registry;
        $this->workflowConfiguration = $workflowConfiguration;
        $this->router                = $router;
        $this->propertyAccess        = $propertyAccess;
    }

    /**
     * @param string $resourceClass
     * @param string $workflowName
     *
     * @return WorkflowConfiguration
     */
    public function getWorkflowConfigurationFor(string $resourceClass, string $workflowName): WorkflowConfiguration
    {
        foreach ($this->workflowConfiguration as $config) {
            if ($resourceClass === $config->getClassName() && $workflowName === $config->getName()) {
                return $config;
            }
        }

        throw new LogicException(sprintf('Workflow %s not found for resource class %s', $workflowName, $resourceClass));
    }

    /**
     * @param $subject
     * @param string $workflowName
     * @param string $transitionName
     *
     * @throws NotEnabledTransitionException
     *
     * @return \Symfony\Component\Workflow\Marking
     */
    public function tryToApply($subject, string $workflowName, string $transitionName)
    {
        $workflow = $this->registry->get($subject, $workflowName);

        if ($workflow->can($subject, $transitionName)) {
            return $workflow->apply($subject, $transitionName);
        }

        throw new NotEnabledTransitionException(
            $subject,
            $transitionName,
            $workflow,
            $workflow->buildTransitionBlockerList($subject, $transitionName)
        );
    }

    /**
     * Returns an array of transitions, both enabled and not enabled, keyed by transition name.
     *
     * @param object $subject
     * @param array  $workflowNames
     *
     * @return Action[]
     */
    public function getAllActions($subject, array $workflowNames = [])
    {
        $resourceClass = get_class($subject);
        $resourceShortName = substr($resourceClass, strrpos($resourceClass, '\\') + 1);
        $workflows = $this->registry->all($subject);
        $actions   = [];

        foreach ($workflows as $workflow) {
            if (!empty($workflowNames) && !in_array($workflow->getName(), $workflowNames, true)) {
                continue;
            }

            // This method only returns transitions that can currently be applied
            // Add enabled transitions to array
            /** @var Transition $transition */
            foreach ($workflow->getEnabledTransitions($subject) as $transition) {
                $transitionMeta = $workflow->getMetadataStore()->getTransitionMetadata($transition);

                $blockers = $workflow->buildTransitionBlockerList($subject, $transition->getName());

                $routeName =  RouteNameGenerator::generate('patch', $resourceShortName, OperationType::ITEM);

                $route = $this->router->getRouteCollection()->get($routeName);

                $url = $this
                    ->router
                    ->generate(
                        $routeName,
                        [
                            'id' => $this->propertyAccess->getValue($subject, 'id'),
                            'workflow' => $workflow->getName(),
                            'transition' => $transition->getName()
                        ],
                        RouterInterface::ABSOLUTE_PATH
                    )
                ;
                $entryPoint = new EntryPoint();
                $entryPoint->setUrl($url);
                $entryPoint->setHttpMethod($route->getMethods()[0]);

                $currentAction = new Action();
                $currentAction->setTarget($entryPoint);
                $currentAction->setName($transition->getName());
                $currentAction->setDescription($transitionMeta['description'] ?? ucfirst($transition->getName()).' Action');

                if (!$blockers->isEmpty()) {
                    foreach ($blockers as $blocker) {
                        // @TODO: add as violation constraint interface
                    }
                }
                $actions[] = $currentAction;
            }
        }

        return $actions;
    }

    public function supportsResource(?string $resourceClass): bool
    {
        foreach ($this->workflowConfiguration as $config) {
            if ($config->getClassName() === $resourceClass) {
                return true;
            }
        }

        return false;
    }

    public function getOperationsFor(string $resourceClass): array
    {
        $resourceShortName = substr($resourceClass, strrpos($resourceClass, '\\') + 1);
        // @TODO: how to get access to resource metadata

        $operations = [];

        foreach ($this->getWorkflowConfigurationForClass($resourceClass) as $workflowConfiguration) {
            $def      = $workflowConfiguration->getDefinition();
            // @TODO: allow overriding custom defaults with workflow metadata
//            $metadata = $def->getMetadataStore()->getWorkflowMetadata();
            foreach ($def->getTransitions() as $transition) {
                // @TODO: allow overriding custom defaults with transition metadata
//                $transitionMeta = $def->getMetadataStore()->getTransitionMetadata($transition);


            }
        }

        return $operations;
    }

    /**
     * @param string $resourceClass
     *
     * @return WorkflowConfiguration[]
     */
    private function getWorkflowConfigurationForClass(string $resourceClass): array
    {
        $configs = [];
        foreach ($this->workflowConfiguration as $config) {
            if ($config->getClassName() === $resourceClass) {
                $configs[] = $config;
            }
        }

        return $configs;
    }
}
