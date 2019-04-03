<?php declare(strict_types=1);

namespace Wesnick\Workflow\Transformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use Wesnick\Workflow\Model\WorkflowDTO;

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
        return $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];
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
