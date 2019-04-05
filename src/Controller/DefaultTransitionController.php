<?php declare(strict_types=1);

namespace Wesnick\Workflow\Controller;

use Wesnick\Workflow\WorkflowManager;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;

/**
 * Class DefaultTransitionController.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class DefaultTransitionController
{
    private $workflowManager;

    public function __construct(WorkflowManager $workflowExecutor)
    {
        $this->workflowManager = $workflowExecutor;
    }

    public function __invoke($data, $subject, string $workflow, string $transition)
    {
        if (!is_object($subject)) {
            throw new BadRequestHttpException(sprintf('Expected object for workflow %s, got %s.', $workflow, gettype($subject)));
        }

        try {
            $this->workflowManager->tryToApply($subject, $workflow, $transition);
        } catch (NotEnabledTransitionException $e) {
            throw new BadRequestHttpException(sprintf('Transition %s in Workflow %s is not available.', $workflow, $transition));
        }

        return $subject;
    }
}
