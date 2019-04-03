<?php declare(strict_types=1);

namespace Wesnick\Workflow\Validation;

use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * Class WorkflowValidationStrategy.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowValidationStrategy implements WorkflowValidationStrategyInterface
{
    public function getValidationGroupsForSubject($subject, WorkflowInterface $workflow, Transition $transition): array
    {
        $groups = array_map(function ($state) use ($workflow) {
            return $workflow->getName().'_'.$state;
        }, $transition->getTos());

        return array_unshift($groups, 'Default', $workflow->getName());
    }
}
