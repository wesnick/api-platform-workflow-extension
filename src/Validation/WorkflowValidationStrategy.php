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

        array_unshift($groups, 'Default', $workflow->getName());

        return $groups;
    }
}
