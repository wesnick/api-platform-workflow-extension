<?php declare(strict_types=1);

namespace Wesnick\Workflow\Model;

use ApiPlatform\Core\Annotation\ApiProperty;
use Wesnick\Workflow\Model\ActionStatusType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An action performed by a direct agent and indirect participants upon a direct object. Optionally happens at a location with the help of an inanimate instrument. The execution of the action may produce a result. Specific action sub-type documentation specifies the exact expectation of each argument/role.\\n\\nSee also \[blog post\](http://blog.schema.org/2014/04/announcing-schemaorg-actions.html) and \[Actions overview document\](http://schema.org/docs/actions.html).
 *
 * @see http://schema.org/Action Documentation on Schema.org
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class Action
{
    /**
     * @var string|null indicates the current disposition of the Action
     *
     * @ApiProperty(iri="http://schema.org/actionStatus")
     * @Groups({"workflowAction:output"})
     * @Assert\Type(type="string")
     * @Assert\Choice(callback={ActionStatusType::class, "toArray"})
     */
    private $actionStatus;

    /**
     * @var EntryPoint|null indicates a target EntryPoint for an Action
     *
     * @ApiProperty(iri="http://schema.org/target")
     * @Groups({"workflowAction:output"})
     */
    private $target;

    /**
     * @var string|null the name of the item
     *
     * @ApiProperty(iri="http://schema.org/name")
     * @Groups({"workflowAction:output"})
     * @Assert\Type(type="string")
     */
    private $name;

    /**
     * @var string|null a description of the item
     *
     * @ApiProperty(iri="http://schema.org/description")
     * @Groups({"workflowAction:output"})
     * @Assert\Type(type="string")
     */
    private $description;

    public function setActionStatus(?string $actionStatus): void
    {
        $this->actionStatus = $actionStatus;
    }

    public function getActionStatus(): ?string
    {
        return $this->actionStatus;
    }

    /**
     * @return EntryPoint|null
     */
    public function getTarget(): ?EntryPoint
    {
        return $this->target;
    }

    /**
     * @param EntryPoint $target
     */
    public function setTarget(EntryPoint $target): void
    {
        $this->target = $target;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
