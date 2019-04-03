<?php declare(strict_types=1);

/*
 * Copyright (c) 2019, Wesley O. Nichols
 */

namespace Wesnick\Workflow\PathResolver;

use ApiPlatform\Core\PathResolver\OperationPathResolverInterface;

/**
 * Class WorkflowActionPathResolver.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowActionPathResolver implements OperationPathResolverInterface
{
    private $decorated;

    public function __construct(OperationPathResolverInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function resolveOperationPath(string $resourceShortName, array $operation, $operationType/*, string $operationName = null*/): string
    {
        $path = $this->decorated->resolveOperationPath($resourceShortName, $operation, $operationType);

        if (!isset($operation['_path_suffix'])) {
            return $path;
        }

        return str_replace('{id}', '{id}'.$operation['_path_suffix'], $path);
    }
}
