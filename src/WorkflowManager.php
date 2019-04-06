<?php declare(strict_types=1);

/*
 * Copyright (c) 2019, Wesley O. Nichols
 */

namespace Wesnick\Workflow;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Wesnick\Workflow\Model\Action;
use Wesnick\Workflow\Model\EntryPoint;
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
     * @var IriConverterInterface
     */
    private $router;

    /**
     * Classmap
     *
     * [ className => [workflowName]]
     *
     * @var array
     */
    private $workflowConfiguration;

    /**
     * WorkflowManager constructor.
     *
     * @param Registry                  $registry
     * @param IriConverterInterface     $router
     * @param array                     $workflowConfiguration
     */
    public function __construct(Registry $registry, IriConverterInterface $router, array $workflowConfiguration)
    {
        $this->registry              = $registry;
        $this->workflowConfiguration = $workflowConfiguration;
        $this->router                = $router;
    }

    /**
     * @param $subject
     * @param string $workflowName
     * @param string $transitionName
     * @param array $context
     *
     * @return \Symfony\Component\Workflow\Marking
     */
    public function tryToApply($subject, string $workflowName, string $transitionName, array $context = [])
    {
        $workflow = $this->registry->get($subject, $workflowName);

        if ($workflow->can($subject, $transitionName)) {
            return $workflow->apply($subject, $transitionName /*, $context */);
        }

        throw new NotEnabledTransitionException(
            $subject,
            $transitionName,
            $workflow,
            $workflow->buildTransitionBlockerList($subject, $transitionName)
        );
    }

    /**
     * Returns an array of enabled transitions, both available and blocked.
     *
     * @param object $subject
     * @param array  $workflowNames
     *
     * @return Action[]
     */
    public function getAllActions($subject, array $workflowNames = [])
    {
        $workflows = $this->registry->all($subject);
        $actions   = [];

        foreach ($workflows as $workflow) {
            if (!empty($workflowNames) && !in_array($workflow->getName(), $workflowNames, true)) {
                continue;
            }

            /** @var Transition $transition */
            foreach ($workflow->getEnabledTransitions($subject) as $transition) {
                $transitionMeta = $workflow->getMetadataStore()->getTransitionMetadata($transition);

                $blockers = $workflow->buildTransitionBlockerList($subject, $transition->getName());

                $url = sprintf(
                    '%s?%s',
                    $this->router->getIriFromItem($subject, UrlGeneratorInterface::ABS_URL),
                    http_build_query([
                    'workflow' => $workflow->getName(),
                    'transition' => $transition->getName()
                    ])
                );

                $entryPoint = new EntryPoint();
                $entryPoint->setUrl($url);
                $entryPoint->setHttpMethod('PATCH');

                $currentAction = new Action();
                $currentAction->setTarget($entryPoint);
                $currentAction->setName($transition->getName());
                $currentAction->setDescription($transitionMeta['description'] ?? ucfirst($transition->getName()).' Action');

                if (!$blockers->isEmpty()) {
                    foreach ($blockers as $blocker) {
                        $parameters = $blocker->getParameters();

                        if (array_key_exists('original_violation', $parameters)) {
                            $violation = $parameters['original_violation'];
                        } else {
                            // @TODO: add a factory or event for building Violations from TransitionBlockers
                            $violation = new ConstraintViolation(
                                $blocker->getMessage(),
                                $blocker->getMessage(),
                                $blocker->getParameters(),
                                $subject,
                                '/',
                                ''
                            );
                        }

                        $currentAction->addError($violation);
                    }
                }
                $actions[] = $currentAction;
            }
        }

        return $actions;
    }
}
