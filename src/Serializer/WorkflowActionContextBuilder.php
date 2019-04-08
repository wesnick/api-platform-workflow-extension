<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Wesnick\WorkflowBundle\Model\PotentialActionInterface;

/**
 * Class WorkflowActionContextBuilder.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowActionContextBuilder implements SerializerContextBuilderInterface
{
    private $decorated;

    public function __construct(SerializerContextBuilderInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (is_a($resourceClass, PotentialActionInterface::class, true)
            && isset($context['groups'])
            && true === $normalization
        ) {
            $context['groups'][] = 'workflowAction:output';
        }

        return $context;
    }
}
