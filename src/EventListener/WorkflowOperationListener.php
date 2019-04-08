<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\EventListener;

use ApiPlatform\Core\Util\RequestAttributesExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Listen to API Platform requests, after the ReadListener (priority=4) and DeserializeListener (priority=2)
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowOperationListener
{
    /**
     * Classmap.
     *
     * [ className => [workflowName]]
     *
     * @var array
     */
    private $enabledWorkflowMap;

    public function __construct(array $enabledWorkflowMap)
    {
        $this->enabledWorkflowMap = $enabledWorkflowMap;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->isMethod(Request::METHOD_PATCH)
            || !($attributes = RequestAttributesExtractor::extractAttributes($request))
            || 'patch' !== $attributes['item_operation_name'] ?? null
            || !array_key_exists($attributes['resource_class'] ?? 'n/a', $this->enabledWorkflowMap)
        ) {
            return;
        }

        $requestContent = json_decode($request->getContent());
        // Set the data attribute as subject, since the DTO will be deserialized to the data attribute
        $request->attributes->set('subject', $request->attributes->get('data'));
        $request->attributes->set('workflowName', $request->query->get('workflow'));
        $request->attributes->set('transitionName', $requestContent->transition);
    }
}
