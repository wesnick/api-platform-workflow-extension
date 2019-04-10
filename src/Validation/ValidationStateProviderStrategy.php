<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\Validation;

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
     * {@inheritdoc}
     */
    public function getValidationGroupsForSubject($subject, WorkflowInterface $workflow, Transition $transition): array
    {
        $groups = [];

        if ($subject instanceof ValidationStateProviderInterface) {
            foreach ($transition->getTos() as $state) {
                $groups = array_merge($groups, $subject->getGroupSequenceForState($state, $workflow->getName()));
            }
        }

        return $groups;
    }
}
