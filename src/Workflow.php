<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess;

use Creatortsv\WorkflowProcess\Runner\WorkflowRunner;
use ReflectionException;

class Workflow implements WorkflowInterface
{
    /**
     * @var array<callable>
     */
    protected array $stages;

    public function __construct(callable ...$stages)
    {
        $this->setStages(...$stages);
    }

    /**
     * @throws ReflectionException
     */
    public function __invoke(...$context): array
    {
        $this->makeRunner(...$context)->run();

        return $context;
    }

    /**
     * @inheritdoc
     */
    public function getStages(): array
    {
        return $this->stages;
    }

    public function setStages(callable ...$stages): Workflow
    {
        $this->stages = $stages;

        return $this;
    }

    public function fresh(callable ...$stages): Workflow
    {
        $stages = $stages ?: $this->getStages();

        return new static(...$stages);
    }

    /**
     * @template T
     * @param T ...$context
     * @throws ReflectionException
     */
    final public function makeRunner(...$context): WorkflowRunner
    {
        return new WorkflowRunner($context, ...$this->stages);
    }
}
