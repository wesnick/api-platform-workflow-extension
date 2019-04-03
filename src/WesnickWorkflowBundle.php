<?php

namespace Wesnick\Workflow;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Wesnick\Workflow\DependencyInjection\Compiler\WorkflowPass;

class WesnickWorkflowBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new WorkflowPass());
    }
}
