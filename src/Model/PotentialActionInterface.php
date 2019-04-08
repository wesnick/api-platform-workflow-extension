<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\Model;

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
