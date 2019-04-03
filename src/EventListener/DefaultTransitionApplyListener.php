<?php declare(strict_types=1);

namespace Wesnick\Workflow\EventListener;

use Symfony\Component\HttpFoundation\Request;
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

    public function __invoke($data, Request $request)
    {
        $input = json_decode($request->getContent(), true);
        if (!is_object($data)) {
            throw new BadRequestHttpException(sprintf('Expected object for workflow %s, got %s.', $workflow, gettype($data)));
        }
        try {
            $this->workflowManager->tryToApply($data, $input['workflow'], $input['transition']);
        } catch (NotEnabledTransitionException $e) {
            throw new BadRequestHttpException(sprintf('Transition %s in Workflow %s is not available.', $workflow, $transition));
        }

        return $data;
    }
}
