<?php declare(strict_types=1);

namespace Wesnick\Workflow\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Wesnick\Workflow\WorkflowManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WorkflowActionContextBuilder.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowActionContextBuilder implements SerializerContextBuilderInterface
{
    private $decorated;
    private $workflowManager;

    public function __construct(WorkflowManager $workflowManager, SerializerContextBuilderInterface $decorated)
    {
        $this->workflowManager = $workflowManager;
        $this->decorated = $decorated;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (
            $this->workflowManager->supportsResource($resourceClass)
            && isset($context['groups'])
            && false === $normalization
        ) {
            $context['groups'][] = 'workflowAction:output';
        }

        return $context;
    }
}
