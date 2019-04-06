<?php declare(strict_types=1);

namespace Wesnick\Workflow\Tests\Fixtures;

use ApiPlatform\Core\Annotation\ApiResource;
use Wesnick\Workflow\Model\PotentialActionInterface;
use Wesnick\Workflow\Model\PotentialActionsTrait;

/**
 * Class ArticleWithWorkflow.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 * @ApiResource()
 */
class ArticleWithWorkflow implements PotentialActionInterface
{
    use PotentialActionsTrait;
}
