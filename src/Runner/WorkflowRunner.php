<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Runner;

use ArrayIterator;
use Creatortsv\WorkflowProcess\Artifacts\ArtifactsInjector;
use Creatortsv\WorkflowProcess\Artifacts\ArtifactsStorage;
use Creatortsv\WorkflowProcess\Stage\StageSwitcher;
use Creatortsv\WorkflowProcess\Utils\CallbackWrapper;
use ReflectionException;

/**
 * @template T
 */
final class WorkflowRunner
{
    private ArrayIterator $stages;
    private StageSwitcher $switcher;
    private ArtifactsInjector $injector;

    /**
     * @param array<T> $context
     * @throws ReflectionException
     */
    public function __construct(array $context, callable ...$stages)
    {
        $this->stages = new ArrayIterator();

        $numbers = [];

        foreach ($stages as $stage) {
            $name = CallbackWrapper::of($stage)->name();
            $numbers[$name] ??= 0;
            $numbers[$name] ++ ;

            $this->stages->append(CallbackWrapper::of($stage, $numbers[$name]));
        }

        $this->switcher = new StageSwitcher($this->stages);

        $storage = new ArtifactsStorage();
        $context = [$this->switcher, $storage, ...$context];

        array_walk($context, fn ($payload) => $storage->set($payload));

        $this->injector = new ArtifactsInjector($storage);
    }

    /**
     * @throws ReflectionException
     */
    public function run(): WorkflowRunner
    {
        $stage = $this
            ->stages
            ->current();

        if ($stage !== null) {
            $name = $stage->name();
            $data = $this->injector->injectInto($stage)();
            $this->switcher->switch();

            if ($data !== null
                && !$data instanceof ArtifactsStorage
                && !$data instanceof StageSwitcher) {
                $this->injector->getStorage()->set($data, $name);
            }

            return $this->run();
        }

        $this->switcher->switch();

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
