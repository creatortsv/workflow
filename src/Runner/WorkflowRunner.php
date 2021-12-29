<?php

declare(strict_types=1);

namespace Creatortsv\Workflow\Runner;

use ArrayIterator;
use Creatortsv\Workflow\Artifacts\ArtifactsInjector;
use Creatortsv\Workflow\Artifacts\ArtifactsStorage;
use Creatortsv\Workflow\Processor\Processor;
use Creatortsv\Workflow\Utils\CallbackWrapper;
use League\Pipeline\Pipeline;
use ReflectionException;

/**
 * @template T
 */
final class WorkflowRunner
{
    private ArrayIterator $stages;
    private ArrayIterator $context;
    private ArtifactsInjector $injector;

    /**
     * @param array<T> $context
     */
    public function __construct(
        array $context,
        CallbackWrapper ...$stages
    ) {
        $this->injector = new ArtifactsInjector(new ArtifactsStorage());
        $this->stages = new ArrayIterator($stages);
        $this->context = new ArrayIterator($context);
    }

    public function run(): WorkflowRunner
    {
        $pipeline = new Pipeline(new Processor($this->injector), ...$this->stages->getArrayCopy());
        $pipeline->process($this->context);

        return $this;
    }

    /**
     * @template T
     * @return T
     * @throws ReflectionException
     */
    public function then(callable $callback)
    {
        return $this
            ->injector
            ->injectInto($callback)();
    }
}
