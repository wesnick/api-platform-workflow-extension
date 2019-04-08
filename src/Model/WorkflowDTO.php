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
