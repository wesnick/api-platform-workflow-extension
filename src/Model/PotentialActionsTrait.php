<?php declare(strict_types=1);

namespace Wesnick\Workflow\Model;

use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Trait PotentialActionsTrait.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
trait PotentialActionsTrait
{
    /**
     * @var Action[] collection of potential Action, which describes an idealized action in which this thing
     * would play an 'object' role
     *
     * @ApiProperty(
     *     iri="http://schema.org/potentialAction",
     *     readable=true,
     *     writable=false
     * )
     * @Groups({"workflowAction:output"})
     */
    private $potentialAction = [];

    /**
     * @param Action $action
     */
    public function addPotentialAction(Action $action)
    {
        $this->potentialAction[] = $action;
    }

    /**
     * @return array
     */
    public function getPotentialAction(): array
    {
        return $this->potentialAction;
    }
}
