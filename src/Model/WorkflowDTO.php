<?php declare(strict_types=1);

namespace Wesnick\Workflow\Model;

/**
 * Default WorkflowDTO.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowDTO
{
    /**
     * @var string
     */
    protected $transition;

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
