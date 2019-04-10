<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Workflow\MarkingStore\MultipleStateMarkingStore;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Tests\WorkflowBuilderTrait;
use Symfony\Component\Workflow\Workflow;
use Wesnick\WorkflowBundle\Controller\DefaultTransitionController;
use Wesnick\WorkflowBundle\Model\WorkflowDTO;

/**
 * Class DefaultTransitionControllerTest.
 */
class DefaultTransitionControllerTest extends TestCase
{
    use WorkflowBuilderTrait;

    public function testInvalidSubjectThrowsException()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $workflow = new Workflow($definition, new MultipleStateMarkingStore());

        $registry = $this->createRegistry($workflow);
        $controller = new DefaultTransitionController($registry->reveal());

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Expected object for workflow "workflow", got array');
        $controller(new WorkflowDTO(), [], 'workflow', 'transition');
    }

    /**
     * @dataProvider workflowProvider
     */
    public function test__invoke($workflow, $subject, $transition, bool $success)
    {
        $registry = $this->createRegistry($workflow, $success);
        $controller = new DefaultTransitionController($registry->reveal());

        if (!$success) {
            $this->expectException(BadRequestHttpException::class);
            $this->expectExceptionMessage(sprintf('Transition "%s" in Workflow "workflow" is not available.', $transition));
        }

        $result = $controller(new WorkflowDTO(), $subject, 'workflow', $transition);

        $this->assertSame($subject, $result);
        $this->assertFalse($workflow->can($subject, $transition));
    }

    public function workflowProvider()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $workflow = new Workflow($definition, new MultipleStateMarkingStore());

        $subject = new \stdClass();
        $subject->marking = null;
        yield [$workflow, $subject, 't1', true];

        $subject = new \stdClass();
        $subject->marking = null;
        yield [$workflow, $subject, 't2', false];

        $subject = new \stdClass();
        $subject->marking = ['b' => 1];
        yield [$workflow, $subject, 't1', false];

        $subject = new \stdClass();
        $subject->marking = ['b' => 1];
        yield [$workflow, $subject, 't2', false];

        $subject = new \stdClass();
        $subject->marking = ['b' => 1, 'c' => 1];
        yield [$workflow, $subject, 't1', false];

        $subject = new \stdClass();
        $subject->marking = ['b' => 1, 'c' => 1];
        yield [$workflow, $subject, 't2', true];

        $subject = new \stdClass();
        $subject->marking = ['f' => 1];
        yield [$workflow, $subject, 't5', false];

        $subject = new \stdClass();
        $subject->marking = ['f' => 1];
        yield [$workflow, $subject, 't6', true];
    }

    private function createRegistry(Workflow $workflow)
    {
        $registry = $this->prophesize(Registry::class);
        $registry->get(Argument::type(\stdClass::class), Argument::type('string'))
            ->willReturn($workflow)
        ;

        return $registry;
    }
}
