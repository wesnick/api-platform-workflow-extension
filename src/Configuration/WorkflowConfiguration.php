<?php declare(strict_types=1);

namespace Wesnick\Workflow\Configuration;

use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Metadata\MetadataStoreInterface;

/**
 * Class WorkflowConfiguration.
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class WorkflowConfiguration
{
    /**
     * The name of the workflow.
     *
     * @var string
     */
    private $name;

    /**
     * The class of the subject.
     *
     * @var string
     */
    private $className;

    /**
     * @var Definition
     */
    private $definition;

    /**
     * @var MetadataStoreInterface
     */
    private $metadataStorage;

    /**
     * WorkflowConfiguration constructor.
     *
     * @param string                 $name
     * @param string                 $className
     * @param Definition             $definition
     * @param MetadataStoreInterface $metadataStorage
     */
    public function __construct(string $name, string $className, Definition $definition, MetadataStoreInterface $metadataStorage)
    {
        $this->name            = $name;
        $this->className       = $className;
        $this->definition      = $definition;
        $this->metadataStorage = $metadataStorage;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return Definition
     */
    public function getDefinition(): Definition
    {
        return $this->definition;
    }

    /**
     * @return MetadataStoreInterface
     */
    public function getMetadataStorage(): MetadataStoreInterface
    {
        return $this->metadataStorage;
    }
}
