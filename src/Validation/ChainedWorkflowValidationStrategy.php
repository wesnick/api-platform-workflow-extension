<?php declare(strict_types=1);

namespace Wesnick\Workflow\Validation;

use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * Class ChainedWorkflowValidationStrategy.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class ChainedWorkflowValidationStrategy implements WorkflowValidationStrategyInterface
{
    /**
     * @var WorkflowValidationStrategyInterface[]
     */
    private $strategies;

    /**
     * ChainedWorkflowValidationStrategy constructor.
     * @param array $strategies
     */
    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationGroupsForSubject($subject, WorkflowInterface $workflow, Transition $transition): array
    {
        $groups = [];

        foreach ($this->strategies as $strategy) {
            $groups = array_merge($groups, $strategy->getValidationGroupsForSubject($subject, $workflow, $transition));
        }

        return array_unique($groups);
    }
}
