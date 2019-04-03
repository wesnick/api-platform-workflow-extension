<?php declare(strict_types=1);

/*
 * Copyright (c) 2019, Wesley O. Nichols
 */

namespace Wesnick\Workflow\Metadata;

use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use Wesnick\Workflow\Listener\DefaultTransitionApplyListener;
use Wesnick\Workflow\Model\WorkflowDTO;
use Wesnick\Workflow\WorkflowManager;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Registry;

/**
 * Ensure psuedo-property potentialActions appears on supported resources and add transition operations to the resource.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowActionsResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    private $workflowManager;
    private $decorated;

    public function __construct(WorkflowManager $workflowManager, ResourceMetadataFactoryInterface $decorated)
    {
        $this->workflowManager = $workflowManager;
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $resourceClass): ResourceMetadata
    {
        $resourceMetadata = $this->decorated->create($resourceClass);
        if (!$this->workflowManager->supportsResource($resourceClass)) {
            return $resourceMetadata;
        }

        // Set the pseudo-group for potentialAction to appear in name collection
        $attributes = $resourceMetadata->getAttributes();
        $attributes['denormalization_context']['groups'][] = 'workflowAction:output';

        $operations = $resourceMetadata->getItemOperations();

        $operations['patch'] = [
            'method'       => 'PATCH',
            # this could be enabled optionally to allow non-workflow related PATCH ops
//            '_path_suffix' => '/'.str_replace('_', '-', $transition->getName()),
            'controller'   => DefaultTransitionApplyListener::class,
//            'defaults'     => [
//                'workflow'   => $workflowConfiguration->getName(),
//                'transition' => $transition->getName(),
//            ],
            'input'  => ['class' => WorkflowDTO::class, 'name' => 'WorkflowDTO'],
        ];


        $newOperations = $this->workflowManager->getOperationsFor($resourceClass);

        return $resourceMetadata
            ->withAttributes($attributes)
            ->withItemOperations(array_merge($operations, $newOperations))
        ;
    }
}
