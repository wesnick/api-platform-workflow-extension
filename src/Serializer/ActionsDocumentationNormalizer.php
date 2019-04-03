<?php declare(strict_types=1);

namespace Wesnick\Workflow\Serializer;

use ApiPlatform\Core\Hydra\Serializer\DocumentationNormalizer;
use Wesnick\Workflow\WorkflowManager;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class ActionsDocumentationNormalizer.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class ActionsDocumentationNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public const FORMAT = 'jsonld';

    private $decorated;
    /**
     * @var array
     */
    private $workflowManager;

    public function __construct(WorkflowManager $workflowManager, DocumentationNormalizer $decorated)
    {
        $this->workflowManager = $workflowManager;
        $this->decorated       = $decorated;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);

        // Adjust hydra:supportedOperation information
        foreach ($object->getResourceNameCollection() as $index => $coll) {
            // Filter out duplicates
            $seen = [];
            $data['hydra:supportedClass'][$index]['hydra:supportedOperation'] = array_filter(
                $data['hydra:supportedClass'][$index]['hydra:supportedOperation'],
                function ($op) use (&$seen) {
                    if (!in_array($op['hydra:method'], $seen, true)) {
                        $seen[] = $op['hydra:method'];

                        return true;
                    }

                    return false;
                }
            );

            // Add in our worklfow methods
            foreach ($this->workflowManager->getOperationsFor($coll) as $route) {
                $hydraOperation = [
                    '@type'       => ['hydra:Operation', 'schema:ControlAction'],
                    'hydra:title' => sprintf('Controls the %s resource.', $data['hydra:supportedClass'][$index]['hydra:title']),
                    'hydra:method'=> 'PUT',
                    'rdfs:label'  => $route['defaults']['transition'],
                    'returns'     => $data['hydra:supportedClass'][$index]['@id'],
                    'expects'     => '#EmptyWorkflow',
                ];

                $data['hydra:supportedClass'][$index]['hydra:supportedOperation'][] = $hydraOperation;
            }
        }

        // Add in our empty payload class
        $data['hydra:supportedClass'][] = [
            '@id'               => '#EmtpyWorkflow',
            '@type'             => 'hydra:Class',
            'hydra:title'       => 'Empty',
            'hydra:label'       => 'Empty',
            'hydra:description' => 'Represents and empty body payload',
        ];

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}
