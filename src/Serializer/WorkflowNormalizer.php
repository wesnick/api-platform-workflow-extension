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
use Wesnick\WorkflowBundle\Model\PotentialActionInterface;
use Wesnick\WorkflowBundle\WorkflowActionGenerator;

/**
 * Class WorkflowNormalizer.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private $decorated;
    private $customNormalizer;
    private $workflowActions;

    public function __construct(
        NormalizerInterface $decorated,
        NormalizerInterface $customNormalizer,
        WorkflowActionGenerator $workflowActions
    ) {
        $this->decorated = $decorated;
        $this->customNormalizer = $customNormalizer;
        $this->workflowActions = $workflowActions;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
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
        if ($object instanceof PotentialActionInterface) {
            $actions = $this->workflowActions->getActionsForSubject($object);
            foreach ($actions as $action) {
                $object->addPotentialAction($action);
            }
        }

        return $this->decorated->normalize($object, $format, $context);
    }
}
