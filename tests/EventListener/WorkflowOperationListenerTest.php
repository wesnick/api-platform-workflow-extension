<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Wesnick\WorkflowBundle\Tests\Fixtures\ArticleWithWorkflow;

class WorkflowOperationListenerTest extends TestCase
{
    private static $classmap = [
        ArticleWithWorkflow::class => [
            'workflow',
            'another_workflow',
        ],
    ];

    /**
     * @dataProvider eventProvider
     */
    public function testOnKernelRequest($method, $data, $workflowName, $transitionName, $query, $body, $hasParams)
    {
        $eventProphecy = $this->prophesize(GetResponseEvent::class);

        $request = new Request($query, [], ['data' => $data, '_api_resource_class' => ArticleWithWorkflow::class, '_api_item_operation_name' => strtolower($method)], [], [], [], $body);
        $request->setMethod($method);
        $eventProphecy->getRequest()->willReturn($request)->shouldBeCalled();

        $listener = new WorkflowOperationListener(self::$classmap);
        $listener->onKernelRequest($eventProphecy->reveal());

        if ($hasParams) {
            $this->assertSame($request->attributes->get('subject'), $data);
            $this->assertSame($request->attributes->get('workflowName'), $workflowName);
            $this->assertSame($request->attributes->get('transitionName'), $transitionName);
        } else {
            $this->assertFalse($request->attributes->has('subject'));
            $this->assertFalse($request->attributes->has('workflowName'));
            $this->assertFalse($request->attributes->has('transitionName'));
        }
    }

    public function eventProvider()
    {
        yield ['GET', new \stdClass(), 'workflow', 'transition', ['workflow' => 'workflow'], json_encode(['transition' => 'transition']), false];
        yield ['POST', new \stdClass(), 'workflow', 'transition', [], [], false];
        yield ['PATCH', new ArticleWithWorkflow(), 'workflow', 'transition', ['workflow' => 'workflow'], json_encode(['transition' => 'transition']), true];
        yield ['PATCH', new ArticleWithWorkflow(), 'workflow', '', ['workflow' => 'workflow'], json_encode(['xxx' => 'transition']), true];
        yield ['PATCH', new ArticleWithWorkflow(), 'non-existing-workflow-is-ok', '', ['workflow' => 'non-existing-workflow-is-ok'], json_encode(['xxx' => 'transition']), true];
    }
}
