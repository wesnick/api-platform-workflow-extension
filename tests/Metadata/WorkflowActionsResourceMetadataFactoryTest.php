<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\Metadata;

use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use PHPUnit\Framework\TestCase;
use Wesnick\WorkflowBundle\Controller\DefaultTransitionController;
use Wesnick\WorkflowBundle\Model\WorkflowDTO;
use Wesnick\WorkflowBundle\Tests\Fixtures\ArticleWithWorkflow;

/**
 * @group unit
 */
class WorkflowActionsResourceMetadataFactoryTest extends TestCase
{
    /**
     * @dataProvider getMetadata
     */
    public function testCreateOperation(ResourceMetadata $before, ResourceMetadata $after, array $formats = [])
    {
        $decoratedProphecy = $this->prophesize(ResourceMetadataFactoryInterface::class);
        $decoratedProphecy->create(ArticleWithWorkflow::class)->shouldBeCalled()->willReturn($before);
        $this->assertSame($after, (new WorkflowActionsResourceMetadataFactory($decoratedProphecy->reveal()))->create(ArticleWithWorkflow::class));
    }

    public function getMetadata()
    {
        $operations = ['patch' => [
            'method' => 'PATCH',
            'controller' => DefaultTransitionController::class,
            'input' => ['class' => WorkflowDTO::class, 'name' => 'WorkflowDTO'],
        ]];
        $attributes = ['denormalization_context' => ['groups' => ['workflowAction:output']]];

        return [
            // Item operations
            [
                new ResourceMetadata(null, null, null, null, [], null, [], []),
                new ResourceMetadata(null, null, null, $operations, [], $attributes, [], []),
            ],
            [
                new ResourceMetadata(null, null, null, ['patch' => []], [], null, [], []),
                new ResourceMetadata(null, null, null, $operations, [], $attributes, [], []),
            ],
            [
                new ResourceMetadata(null, null, null, [], [], ['denormalization_context' => ['groups' => ['workflowAction:output']]], [], []),
                new ResourceMetadata(null, null, null, $operations, [], $attributes, [], []),
            ],
        ];
    }
}
