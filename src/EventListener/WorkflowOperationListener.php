<?php declare(strict_types=1);

namespace Wesnick\Workflow\EventListener;

use ApiPlatform\Core\Util\RequestAttributesExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class WorkflowOperationListener.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowOperationListener
{
    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->isMethod(Request::METHOD_PATCH)
            || !($attributes = RequestAttributesExtractor::extractAttributes($request))
            || 'patch' !== $attributes['item_operation_name'] ?? null
        ) {
            return;
        }

        $requestContent = json_decode($request->getContent());
        // Set the data attribute as subject,
        // since the DTO will be deserialized to the data attribute
        $request->attributes->set('subject', $request->attributes->get('data'));
        $request->attributes->set('workflow', $request->query->get('workflow'));
        $request->attributes->set('transition', $requestContent->transition);
    }
}
