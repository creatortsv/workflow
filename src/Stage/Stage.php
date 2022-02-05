<?php

namespace Creatortsv\WorkflowProcess\Stage;

use Creatortsv\WorkflowProcess\Support\Helper\CallableInstance;
use Creatortsv\WorkflowProcess\Transition\Transition;

final class Stage
{
    public readonly string $name;
    public readonly CallableInstance $instance;

    /**
     * @var string[]
     */
    public readonly array $artifactNames;

    /**
     * Transitions are used by the process to switch between stages according to both "from" and "to" their properties.
     * Exception will be thrown if more than one of them with the same "from" property value return TRUE
     * Exception won't be thrown if all of them return FALSE
     *
     * @var array<string|null, Transition[]>
     */
    private array $transitions = [];

    public function __construct(
        callable $callable,
        public bool $enabled = true,
        ?string $name = null,
    ) {
        $instance = new CallableInstance($callable);

        $this->instance = $instance;
        $this->name = $name ?? $instance->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function __invoke(mixed ...$parameters): mixed
    {
        return ($this->instance)(...$parameters);
    }

    public function setTransitions(Transition ...$transitions): Stage
    {
        $this->transitions = [];

        foreach ($transitions as $transition) {
            $this->transitions[$transition->from][] = $transition;
        }

        return $this;
    }

    public function addTransitions(Transition ...$transitions): Stage
    {
        $this->setTransitions(...$this->transitions, ...$transitions);

        return $this;
    }

    /**
     * @return Transition[]
     */
    public function getTransitions(?string $from = null): array
    {
        return $this->transitions[$from] ?? [];
    }

    public function setArtifactNames(string ...$names): Stage
    {
        $this->artifactNames = $names;

        return $this;
    }
}
