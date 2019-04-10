<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\Controller;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Registry;
use Wesnick\WorkflowBundle\Model\WorkflowDTO;

/**
 * Class DefaultTransitionController.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class DefaultTransitionController
{
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param WorkflowDTO $data
     * @param mixed $subject
     * @param string $workflowName
     * @param string $transitionName
     *
     * @return mixed
     */
    public function __invoke($data, $subject, string $workflowName, string $transitionName)
    {
        if (!is_object($subject)) {
            throw new BadRequestHttpException(
                sprintf('Expected object for workflow "%s", got %s.', $workflowName, gettype($subject))
            );
        }

        try {
            $workflow = $this->registry->get($subject, $workflowName);

            if ($workflow->can($subject, $transitionName)) {

                // Symfony 4.2 added context to workflow transitions
                if (3 < Kernel::MAJOR_VERSION && 3 < Kernel::MINOR_VERSION) {
                    $workflow->apply($subject, $transitionName, ['wesnick_workflow_dto' => $data]);
                } else {
                    $workflow->apply($subject, $transitionName);
                }

                return $subject;
            }

        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        throw new BadRequestHttpException(
            sprintf('Transition "%s" in Workflow "%s" is not available.', $transitionName, $workflowName)
        );
    }
}
