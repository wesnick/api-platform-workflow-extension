<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\EventListener;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;
use Wesnick\WorkflowBundle\Validation\WorkflowValidationStrategyInterface;

/**
 * Class SubjectValidatorListener.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class SubjectValidatorListener
{
    private $validator;
    private $validationStrategy;

    /**
     * SubjectValidatorListener constructor.
     *
     * @param ValidatorInterface                  $validator
     * @param WorkflowValidationStrategyInterface $validationStrategy
     */
    public function __construct(ValidatorInterface $validator, ?WorkflowValidationStrategyInterface $validationStrategy)
    {
        $this->validator = $validator;
        $this->validationStrategy = $validationStrategy;
    }

    /**
     * @param GuardEvent $event
     */
    public function onGuard(GuardEvent $event)
    {
        $validationGroups = $this
            ->validationStrategy
            ->getValidationGroupsForSubject($event->getSubject(), $event->getWorkflow(), $event->getTransition())
        ;

        $violations = $this->validator->validate($event->getSubject(), null, $validationGroups);

        foreach ($violations as $violation) {
            $event->addTransitionBlocker(
                new TransitionBlocker(
                    $violation->getMessage(),
                    $violation->getCode(),
                    $violation->getParameters() + ['original_violation' => $violation]
                )
            );
        }
    }
}
