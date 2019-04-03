<?php declare(strict_types=1);

namespace Wesnick\Workflow\Serializer;

use Wesnick\Workflow\Model\Action;
use Wesnick\Workflow\Model\EntryPoint;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class WorkflowActionNormalizer.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowActionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private $customNormalizer;

    public function __construct(NormalizerInterface $customNormalizer)
    {
        $this->customNormalizer = $customNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Action || $data instanceof EntryPoint;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        if ($object instanceof Action) {

            $resourceClass = get_class($object);
            $resourceShortName = substr($resourceClass, strrpos($resourceClass, '\\') + 1);
            return [
                    '@context' => 'http://schema.org',
                    '@type'    => $resourceShortName
            ] + array_filter($this->customNormalizer->normalize($object, 'json'));
        } elseif ($object instanceof EntryPoint) {
            $data = array_filter($this->customNormalizer->normalize($object, 'json'));
            // Entrypoint can be represented as an object or a string in case only url property is present
            if (count($data) === 1 && array_key_exists('url', $data)) {
                return $data['url'];
            }

            return $data;
        }

        return null;
    }
}
