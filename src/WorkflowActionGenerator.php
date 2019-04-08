<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;
use Wesnick\WorkflowBundle\Model\Action;
use Wesnick\WorkflowBundle\Model\EntryPoint;

/**
 * Class WorkflowActionGenerator.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowActionGenerator
{
    private $registry;
    private $iriConverter;

    /**
     * Classmap.
     *
     * [ className => [workflowName]]
     *
     * @var array
     */
    private $enabledWorkflowMap;

    /**
     * WorkflowActionGenerator constructor.
     *
     * @param $registry
     * @param $iriConverter
     * @param array $enabledWorkflowMap
     */
    public function __construct(Registry $registry, IriConverterInterface $iriConverter, array $enabledWorkflowMap)
    {
        $this->registry = $registry;
        $this->iriConverter = $iriConverter;
        $this->enabledWorkflowMap = $enabledWorkflowMap;
    }

    public function getActionsForSubject($subject)
    {
        $workflows = $this->registry->all($subject);
        $actions = [];

        foreach ($workflows as $workflow) {
            /** @var Transition $transition */
            foreach ($workflow->getEnabledTransitions($subject) as $transition) {
                $transitionMeta = $workflow->getMetadataStore()->getTransitionMetadata($transition);

                $blockers = $workflow->buildTransitionBlockerList($subject, $transition->getName());

                $url = sprintf(
                    '%s?%s',
                    $this->iriConverter->getIriFromItem($subject, UrlGeneratorInterface::ABS_URL),
                    http_build_query([
                        'workflow' => $workflow->getName(),
                        'transition' => $transition->getName(),
                    ])
                );

                $entryPoint = new EntryPoint();
                $entryPoint->setUrl($url);
                $entryPoint->setHttpMethod('PATCH');

                $currentAction = new Action();
                $currentAction->setTarget($entryPoint);
                $currentAction->setName($transition->getName());
                $currentAction->setDescription($transitionMeta['description'] ?? ucfirst($transition->getName()).' Action');
                // @TODO: add sub status (available, unavailable, access denied, invalid)

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

                $actions[] = $currentAction;
            }
        }

        return $actions;
    }
}
