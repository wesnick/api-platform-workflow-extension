<?php

namespace Wesnick\WorkflowBundle\Tests\Validation;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Workflow\MarkingStore\MultipleStateMarkingStore;
use Symfony\Component\Workflow\Tests\WorkflowBuilderTrait;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Wesnick\WorkflowBundle\Tests\Fixtures\ArticleWithWorkflow;
use Wesnick\WorkflowBundle\Tests\Fixtures\StateProviderWithWorkflow;
use Wesnick\WorkflowBundle\Validation\ChainedWorkflowValidationStrategy;
use Wesnick\WorkflowBundle\Validation\ValidationStateProviderStrategy;
use Wesnick\WorkflowBundle\Validation\WorkflowValidationStrategy;

class ChainedWorkflowValidationStrategyTest extends TestCase
{
    use WorkflowBuilderTrait;

    /**
     * @dataProvider validationSubjectProvider
     */
    public function testGetValidationGroupsForSubject($subject, Workflow $workflow, Transition $transition, array $result)
    {
        $strategy = new ChainedWorkflowValidationStrategy([
            new WorkflowValidationStrategy(),
            new ValidationStateProviderStrategy()
        ]);

        $groups = $strategy->getValidationGroupsForSubject($subject, $workflow, $transition);
        $this->assertEquals($result, $groups);
    }

    public function validationSubjectProvider()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $transitions = $definition->getTransitions();
        $getTransitionByName = function ($name) use ($transitions) {
            foreach ($transitions as $transition) {
                if ($transition->getName() === $name) {
                    return $transition;
                }
            }
        };
        $workflow = new Workflow($definition, new MultipleStateMarkingStore());

        yield [new ArticleWithWorkflow(), $workflow, $getTransitionByName('t1'), ['Default', 'unnamed', 'unnamed_b', 'unnamed_c']];
        yield [new ArticleWithWorkflow(), $workflow, $getTransitionByName('t2'), ['Default', 'unnamed', 'unnamed_d']];
        yield [new StateProviderWithWorkflow('blue'), $workflow, $getTransitionByName('t1'), ['Default', 'unnamed', 'unnamed_b', 'unnamed_c', 'b_blue', 'c_blue']];
        yield [new StateProviderWithWorkflow('red'), $workflow, $getTransitionByName('t1'), ['Default', 'unnamed', 'unnamed_b', 'unnamed_c', 'b_red', 'c_red']];
        yield [new StateProviderWithWorkflow('red'), $workflow, $getTransitionByName('t2'), ['Default', 'unnamed', 'unnamed_d', 'd_red']];
        yield [new StateProviderWithWorkflow('green'), $workflow, $getTransitionByName('t2'), ['Default', 'unnamed', 'unnamed_d', 'd_green']];
    }
}
