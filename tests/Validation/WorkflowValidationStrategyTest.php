<?php

namespace Wesnick\WorkflowBundle\Tests\Validation;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Workflow\MarkingStore\MultipleStateMarkingStore;
use Symfony\Component\Workflow\Tests\WorkflowBuilderTrait;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Wesnick\WorkflowBundle\Tests\Fixtures\ArticleWithWorkflow;
use Wesnick\WorkflowBundle\Validation\WorkflowValidationStrategy;

class WorkflowValidationStrategyTest extends TestCase
{
    use WorkflowBuilderTrait;

    /**
     * @dataProvider validationSubjectProvider
     */
    public function testGetValidationGroupsForSubject($subject, Workflow $workflow, Transition $transition, array $result)
    {
        $strategy = new WorkflowValidationStrategy();
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
    }
}
