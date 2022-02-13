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
     * @var array<string, Transition[]>
     */
    private array $transitions = [];

    /**
     * @var Transition[]
     */
    private array $unclassified = [];

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
        foreach ($transitions as $transition) {
            if ($transition->from) {
                foreach ($transition->from as $name) {
                    $this->transitions[$name][] = $transition;
                }
            } else {
                $this->unclassified[] = $transition;
            }
        }

        return $this;
    }

    public function addTransitions(Transition ...$transitions): Stage
    {
        $this->setTransitions(...$transitions);

        return $this;
    }

    /**
     * @return Transition[]
     */
    public function getTransitions(?string $from = null): array
    {
        $transitions = $this->transitions[$from] ?? [];

        return [
            ...$transitions,
            ...array_filter($this->unclassified, fn (Transition $t): bool => !$from
                || !in_array($from, $t->except, true)),
        ];
    }

    public function setArtifactNames(string ...$names): Stage
    {
        $this->artifactNames = $names;

        return $this;
    }

    public function hasTransitions(): int
    {
        return !empty($this->transitions) || !empty($this->unclassified);
    }
}
