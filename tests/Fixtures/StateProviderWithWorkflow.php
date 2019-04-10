<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\Tests\Fixtures;

use ApiPlatform\Core\Annotation\ApiResource;
use Wesnick\WorkflowBundle\Model\PotentialActionInterface;
use Wesnick\WorkflowBundle\Model\PotentialActionsTrait;
use Wesnick\WorkflowBundle\Validation\ValidationStateProviderInterface;

/**
 * Class ArticleWithWorkflow.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 * @ApiResource()
 */
class StateProviderWithWorkflow implements ValidationStateProviderInterface
{
    private $discriminator;

    /**
     * StateProviderWithWorkflow constructor.
     * @param string $discriminator
     */
    public function __construct(string $discriminator)
    {
        $this->discriminator = $discriminator;
    }


    public function getGroupSequenceForState(string $state, string $workflowName): array
    {
        return [
            sprintf('%s_%s', $state, $this->discriminator)
        ];
    }
}
