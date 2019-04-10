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
use Symfony\Component\Workflow\Workflow;

/**
 * Interface WorkflowValidationStrategyInterface.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
interface WorkflowValidationStrategyInterface
{
    /**
     * Return the validation groups that should be used to validate a subject for a given transition in a given workflow.
     *
     * @param object     $subject
     * @param Workflow   $workflow
     * @param Transition $transition
     *
     * @return array<string>
     */
    public function getValidationGroupsForSubject($subject, Workflow $workflow, Transition $transition): array;
}
