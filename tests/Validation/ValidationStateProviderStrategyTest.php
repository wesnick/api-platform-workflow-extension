<?php

namespace Wesnick\WorkflowBundle\Tests\Validation;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Workflow\MarkingStore\MultipleStateMarkingStore;
use Symfony\Component\Workflow\Tests\WorkflowBuilderTrait;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Wesnick\WorkflowBundle\Tests\Fixtures\StateProviderWithWorkflow;
use Wesnick\WorkflowBundle\Validation\ValidationStateProviderStrategy;

class ValidationStateProviderStrategyTest extends TestCase
{
    use WorkflowBuilderTrait;

    /**
     * @dataProvider validationSubjectProvider
     */
    public function testGetValidationGroupsForSubject($subject, Workflow $workflow, Transition $transition, array $result)
    {
        $strategy = new ValidationStateProviderStrategy();
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

        yield [new \stdClass(), $workflow, $getTransitionByName('t1'), []];
        yield [new StateProviderWithWorkflow('blue'), $workflow, $getTransitionByName('t1'), ['b_blue', 'c_blue']];
        yield [new StateProviderWithWorkflow('red'), $workflow, $getTransitionByName('t1'), ['b_red', 'c_red']];
        yield [new StateProviderWithWorkflow('red'), $workflow, $getTransitionByName('t2'), ['d_red']];
        yield [new StateProviderWithWorkflow('green'), $workflow, $getTransitionByName('t2'), ['d_green']];
    }
}
