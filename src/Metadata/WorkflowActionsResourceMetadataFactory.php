<?php declare(strict_types=1);

/*
 * Copyright (c) 2019, Wesley O. Nichols
 */

namespace Wesnick\Workflow\Metadata;

use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use Wesnick\Workflow\Controller\DefaultTransitionController;
use Wesnick\Workflow\Model\PotentialActionInterface;
use Wesnick\Workflow\Model\WorkflowDTO;

/**
 * Ensure psuedo-property potentialActions appears on supported resources and add transition operations to the resource.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowActionsResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    private $decorated;

    public function __construct(ResourceMetadataFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $resourceClass): ResourceMetadata
    {
        $resourceMetadata = $this->decorated->create($resourceClass);
        if (!is_a($resourceClass, PotentialActionInterface::class, true)) {
            return $resourceMetadata;
        }

        // Set the pseudo-group for potentialAction to appear in name collection metadata factories
        $attributes = $resourceMetadata->getAttributes();
        $groups = $attributes['denormalization_context']['groups'] ?? [];
        if (!in_array('workflowAction:output', $groups, true)) {
            $attributes['denormalization_context']['groups'][] = 'workflowAction:output';
        }

        $operations = $resourceMetadata->getItemOperations();
        $operations['patch'] = [
            'method'       => 'PATCH',
            'controller'   => DefaultTransitionController::class,
            'input'  => ['class' => WorkflowDTO::class, 'name' => 'WorkflowDTO'],
        ];

        return $resourceMetadata
            ->withAttributes($attributes)
            ->withItemOperations($operations)
        ;
    }
}
