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
    private $supportedResources;

    public function __construct(array $supportedResources, SerializerContextBuilderInterface $decorated)
    {
        $this->supportedResources = $supportedResources;
        $this->decorated = $decorated;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (
            array_key_exists($resourceClass, $this->supportedResources)
            && isset($context['groups'])
            && false === $normalization
        ) {
            $context['groups'][] = 'workflowAction:output';
        }

        return $context;
    }
}
