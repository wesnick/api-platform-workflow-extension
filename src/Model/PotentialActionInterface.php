<?php

namespace Wesnick\Workflow\Model;

/**
 * Interface PotentialActionInterface.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
interface PotentialActionInterface
{
    /**
     * @param Action $action
     */
    public function addPotentialAction(Action $action);

    /**
     * @return Action[]
     */
    public function getPotentialAction(): array;
}
