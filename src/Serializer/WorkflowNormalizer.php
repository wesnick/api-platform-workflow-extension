<?php declare(strict_types=1);

namespace Wesnick\Workflow\Serializer;

use Wesnick\Workflow\WorkflowManager;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class WorkflowNormalizer.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private $workflowManager;
    private $itemNormalizer;
    private $customNormalizer;

    public function __construct(
        WorkflowManager $workflowManager,
        NormalizerInterface $itemNormalizer,
        NormalizerInterface $customNormalizer
    ) {
        $this->workflowManager  = $workflowManager;
        $this->itemNormalizer   = $itemNormalizer;
        $this->customNormalizer = $customNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return 'jsonld' === $format
            && is_object($data)
            && $this->workflowManager->supportsResource(get_class($data));
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
        $actions    = $this->workflowManager->getAllActions($object);
        $actionData = [];
        foreach ($actions as $action) {
            $object->addPotentialAction($action);
            $actionData[] = [
                '@context' => 'http://schema.org',
                '@type'    => (new \ReflectionClass($action))->getShortName(),
            ] + $this->customNormalizer->normalize($action, 'json');
        }

        $data                    = $this->itemNormalizer->normalize($object, $format, $context);
        $data['potentialAction'] = $actionData;

        return $data;
    }
}
