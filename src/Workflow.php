<?php

declare(strict_types=1);

namespace Creatortsv\Workflow;

use ArrayIterator;
use Creatortsv\Workflow\Runner\WorkflowRunner;
use Creatortsv\Workflow\Utils\CallbackWrapper;
use ReflectionException;

class Workflow implements WorkflowInterface
{
    /**
     * @var ArrayIterator<callable>
     */
    protected ArrayIterator $stages;

    public function __construct(callable ...$stages)
    {
        $this->setStages(...$stages);
    }

    public function getStages(): ArrayIterator
    {
        return $this->stages;
    }

    public function setStages(callable ...$stages): Workflow
    {
        $this->stages = new ArrayIterator($stages);

        return $this;
    }

    public function fresh(callable ...$stages): Workflow
    {
        return new static(...$stages);
    }

    /**
     * @template T
     * @param T ...$context
     * @throws ReflectionException
     */
    public function makeRunner(...$context): WorkflowRunner
    {
        return new WorkflowRunner($context, ...array_map(fn (callable $stage): CallbackWrapper
            => new CallbackWrapper($stage), $this
            ->stages
            ->getArrayCopy()));
    }
}
