<?php declare(strict_types=1);

namespace Wesnick\Workflow\Listener;

use Wesnick\Workflow\WorkflowManager;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;

/**
 * Class DefaultTransitionApplyListener.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class DefaultTransitionApplyListener
{
    private $workflowManager;

    public function __construct(WorkflowManager $workflowExecutor)
    {
        $this->workflowManager = $workflowExecutor;
    }

    public function __invoke($data, $workflow, $transition)
    {
        if (!is_object($data)) {
            throw new BadRequestHttpException(sprintf('Expected object for workflow %s, got %s.', $workflow, gettype($data)));
        }
        try {
            $this->workflowManager->tryToApply($data, $workflow, $transition);
        } catch (NotEnabledTransitionException $e) {
            throw new BadRequestHttpException(sprintf('Transition %s in Workflow %s is not available.', $workflow, $transition));
        }

        return $data;
    }
}
