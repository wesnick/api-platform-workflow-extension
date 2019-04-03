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

    /**
     * @param array $actions
     *
     * @return array
     */
    protected function getPotentialActions(array $actions): array
    {
        $actionData = [];

        foreach ($actions as $action) {
        }

        return $actionData;
//        foreach ($constraintViolationList as $violation) {
//            $violationData = [
//                'propertyPath' => $this->nameConverter ? $this->nameConverter->normalize($violation->getPropertyPath()) : $violation->getPropertyPath(),
//                'message' => $violation->getMessage(),
//            ];
//
//            $constraint = $violation->getConstraint();
//            if ($this->serializePayloadFields && $constraint && $constraint->payload) {
//                // If some fields are whitelisted, only them are added
//                $payloadFields = null === $this->serializePayloadFields ? $constraint->payload : array_intersect_key($constraint->payload, array_flip($this->serializePayloadFields));
//                $payloadFields && $violationData['payload'] = $payloadFields;
//            }
//
//            $violations[] = $violationData;
//            $messages[] = ($violationData['propertyPath'] ? "{$violationData['propertyPath']}: " : '').$violationData['message'];
//        }
//
//        return [$messages, $violations];
    }
}
