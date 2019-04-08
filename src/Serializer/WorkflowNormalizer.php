<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\Serializer;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;
use Wesnick\WorkflowBundle\Model\Action;
use Wesnick\WorkflowBundle\Model\EntryPoint;
use Wesnick\WorkflowBundle\Model\PotentialActionInterface;

/**
 * Class WorkflowNormalizer.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private $decorated;
    private $customNormalizer;
    private $registry;
    private $iriConverter;

    /**
     * Classmap.
     *
     * [ className => [workflowName]]
     *
     * @var array
     */
    private $enabledWorkflowMap;

    public function __construct(
        NormalizerInterface $decorated,
        NormalizerInterface $customNormalizer,
        Registry $registry,
        IriConverterInterface $iriConverter,
        array $enabledWorkflowMap
    ) {
        $this->decorated = $decorated;
        $this->customNormalizer = $customNormalizer;
        $this->registry = $registry;
        $this->iriConverter = $iriConverter;
        $this->enabledWorkflowMap = $enabledWorkflowMap;
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
        $data = $this->decorated->normalize($object, $format, $context);

        if (!$object instanceof PotentialActionInterface) {
            return $data;
        }

        $actions = $this->getAllActions($object);
        $actionData = [];
        foreach ($actions as $action) {
            $object->addPotentialAction($action);
            $actionData[] = [
                '@context' => 'http://schema.org',
                '@type' => (new \ReflectionClass($action))->getShortName(),
            ] + $this->customNormalizer->normalize($action, 'json');
        }

        $data['potentialAction'] = $actionData;

        return $data;
    }

    /**
     * Returns an array of enabled transitions, both available and blocked.
     *
     * @param object $subject
     * @param array  $workflowNames
     *
     * @return Action[]
     */
    private function getAllActions($subject, array $workflowNames = [])
    {
        $workflows = $this->registry->all($subject);
        $actions = [];

        foreach ($workflows as $workflow) {
            if (!empty($workflowNames) && !in_array($workflow->getName(), $workflowNames, true)) {
                continue;
            }

            /** @var Transition $transition */
            foreach ($workflow->getEnabledTransitions($subject) as $transition) {
                $transitionMeta = $workflow->getMetadataStore()->getTransitionMetadata($transition);

                $blockers = $workflow->buildTransitionBlockerList($subject, $transition->getName());

                $url = sprintf(
                    '%s?%s',
                    $this->iriConverter->getIriFromItem($subject, UrlGeneratorInterface::ABS_URL),
                    http_build_query([
                        'workflow' => $workflow->getName(),
                        'transition' => $transition->getName(),
                    ])
                );

                $entryPoint = new EntryPoint();
                $entryPoint->setUrl($url);
                $entryPoint->setHttpMethod('PATCH');

                $currentAction = new Action();
                $currentAction->setTarget($entryPoint);
                $currentAction->setName($transition->getName());
                $currentAction->setDescription($transitionMeta['description'] ?? ucfirst($transition->getName()) . ' Action');

                if (!$blockers->isEmpty()) {
                    foreach ($blockers as $blocker) {
                        $parameters = $blocker->getParameters();

                        if (array_key_exists('original_violation', $parameters)) {
                            $violation = $parameters['original_violation'];
                        } else {
                            // @TODO: add a factory or event for building Violations from TransitionBlockers
                            $violation = new ConstraintViolation(
                                $blocker->getMessage(),
                                $blocker->getMessage(),
                                $blocker->getParameters(),
                                $subject,
                                '/',
                                ''
                            );
                        }

                        $currentAction->addError($violation);
                    }
                }
                $actions[] = $currentAction;
            }
        }

        return $actions;
    }
}
