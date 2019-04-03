<?php declare(strict_types=1);

namespace Wesnick\Workflow\Validation;

use Surex\Validation\ValidationStateProviderInterface;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * Class ValidationStateProviderStrategy.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class ValidationStateProviderStrategy implements WorkflowValidationStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function getValidationGroupsForSubject($subject, WorkflowInterface $workflow, Transition $transition): array
    {
        $groups = [];

        if ($subject instanceof ValidationStateProviderInterface) {
            foreach ($transition->getTos() as $state) {
                $marking = method_exists(get_class($workflow->getMarkingStore()), 'getProperty') ? $workflow->getMarkingStore()->getProperty() : null;
                $groups = array_merge($groups, $subject->getGroupSequenceForState($state, $marking));
            }
        }

        return $groups;
    }
}
