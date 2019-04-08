<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\Transformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use Wesnick\WorkflowBundle\Model\WorkflowDTO;

/**
 * Class WorkflowDtoTransformer.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowDtoTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($data, string $to, array $context = [])
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if (is_object($data)) {
            return false;
        }

        return WorkflowDTO::class === ($context['input']['class'] ?? null);
    }
}
