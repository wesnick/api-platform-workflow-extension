<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Wesnick\WorkflowBundle\Model\Action;
use Wesnick\WorkflowBundle\Model\EntryPoint;

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
                    '@type' => $resourceShortName,
            ] + array_filter($this->customNormalizer->normalize($object, 'json'));
        } elseif ($object instanceof EntryPoint) {
            $data = array_filter($this->customNormalizer->normalize($object, 'json'));
            // EntryPoint can be represented as an object or a string in case only url property is present
            if (1 === count($data) && array_key_exists('url', $data)) {
                return $data['url'];
            }

            return $data;
        }

        return null;
    }
}
