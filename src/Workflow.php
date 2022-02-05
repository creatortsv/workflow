<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess;

use Creatortsv\WorkflowProcess\Exception\StagesNotFoundException;
use Creatortsv\WorkflowProcess\Exception\WorkflowRequirementsMissedException;
use Creatortsv\WorkflowProcess\Runner\WorkflowRunner;
use Creatortsv\WorkflowProcess\Stage\Stage;
use Creatortsv\WorkflowProcess\Stage\StageFactory;
use Creatortsv\WorkflowProcess\Support\Helper\AttributeReader;
use ReflectionException;

/**
 * This class is used to configure a workflow process
 * Stages, their transitions must be configured with the Workflow class
 *
 * @Workflow
 */
class Workflow implements WorkflowInterface
{
    /**
     * @var Stage[]
     */
    protected array $stages = [];

    public readonly string|null $required;

    /**
     * @throws ReflectionException
     */
    public function __construct(callable ...$stages)
    {
        $attributes = AttributeReader::of($this)->read(attribute: Support\Workflow::class);

        [, $attributes] = array_pop($attributes) ?? [null, []];
        $this->required = array_pop($attributes)?->required;

        foreach ($stages as $stage) {
            $this->stages[] = StageFactory::create(callable: $stage);
        }
    }

    /**
     * @throws StagesNotFoundException
     */
    public function disable(string ...$stages): Workflow
    {
        return $this->toggle(false, ...$stages);
    }

    /**
     * @throws StagesNotFoundException
     */
    public function enable(string ...$stages): Workflow
    {
        return $this->toggle(true, ...$stages);
    }

    /**
     * @throws StagesNotFoundException
     */
    final public function toggle(bool $state, string ...$stages): Workflow
    {
        $found = [];

        foreach ($this->stages as $stage) {
            if (in_array($stage->name, $stages, true)) {
                $stage->enabled = $state;
                $found[] = $stage;
            }
        }

        if ($errors = array_diff($stages, $found)) {
            throw new StagesNotFoundException(...$errors);
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getEnabled(): array
    {
        return array_map(fn (Stage $stage): string => $stage->name, $this->enabled());
    }

    /**
     * @throws WorkflowRequirementsMissedException
     */
    final public function makeRunner(object ...$context): WorkflowRunner
    {
        if ($this->required !== null) {
            $required = array_filter($context, fn (object $object): bool =>
                $this->required === $object::class ||
                    (is_subclass_of($object, $this->required)) ||
                    (in_array($this->required, class_implements($object), true)));

            if (!$required) {
                throw new WorkflowRequirementsMissedException($this);
            }
        }

        return new WorkflowRunner($context, ...$this->enabled());
    }

    /**
     * @return Stage[]
     */
    protected function enabled(): array
    {
        return array_filter($this->stages, fn (Stage $stage): bool => $stage->enabled);
    }
}
