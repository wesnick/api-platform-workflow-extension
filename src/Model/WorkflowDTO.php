<?php declare(strict_types=1);

namespace Wesnick\Workflow\Model;

/**
 * Class WorkflowDTO.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowDTO
{
    /**
     * @var string
     */
    private $workflow;

    /**
     * @var string
     */
    private $transition;

    /**
     * @return string
     */
    public function getWorkflow(): string
    {
        return $this->workflow;
    }

    /**
     * @param string $workflow
     */
    public function setWorkflow(string $workflow): void
    {
        $this->workflow = $workflow;
    }

    /**
     * @return string
     */
    public function getTransition(): string
    {
        return $this->transition;
    }

    /**
     * @param string $transition
     */
    public function setTransition(string $transition): void
    {
        $this->transition = $transition;
    }
}
