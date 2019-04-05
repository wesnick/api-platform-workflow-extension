<?php declare(strict_types=1);

namespace Wesnick\Workflow\Serializer;

use ApiPlatform\Core\Hydra\Serializer\DocumentationNormalizer;
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

    public function __construct(DocumentationNormalizer $decorated)
    {
        $this->decorated       = $decorated;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        // @TODO: review that this is even gets hydra to do anything extra
        $data = $this->decorated->normalize($object, $format, $context);

        // Add in our empty payload class
        $data['hydra:supportedClass'][] = [
            '@id'               => '#WorkflowDTO',
            '@type'             => 'hydra:Class',
            'hydra:title'       => 'WorkflowDTO',
            'hydra:label'       => 'WorkflowDTO',
            'hydra:description' => 'Represents workflow name and transition.',
        ];

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}
