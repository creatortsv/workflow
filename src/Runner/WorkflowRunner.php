<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Runner;

use Creatortsv\WorkflowProcess\Artifacts\ArtifactsInjector;
use Creatortsv\WorkflowProcess\Artifacts\ArtifactsStorage;
use Creatortsv\WorkflowProcess\Stage\Skip;
use Creatortsv\WorkflowProcess\Stage\StageManager;
use Creatortsv\WorkflowProcess\Stage\StageSwitcher;
use ReflectionException;

/**
 * @template T
 */
final class WorkflowRunner
{
    private StageManager $manager;
    private ArtifactsInjector $injector;

    /**
     * @param array<T> $context
     * @throws ReflectionException
     */
    public function __construct(array $context, callable ...$stages)
    {
        $storage = new ArtifactsStorage();

        $this->manager = new StageManager(...$stages);
        $this->injector = new ArtifactsInjector($storage);

        $context = [new StageSwitcher($this->manager), $storage, ...array_values($context)];

        array_walk($context, fn ($artifact) => $storage->set($artifact));
    }

    /**
     * @throws ReflectionException
     */
    public function run(): WorkflowRunner
    {
        $stage = $this
            ->manager
            ->getStages()
            ->current();

        if ($stage !== null) {
            $name = $stage->name();
            $data = $this->injector->injectInto($stage)();

            $this->validateData($data) && $this->injector
                ->getStorage()
                ->set($data, $name);

            if ($stage->getAfterCallback() !== null) {
                $this->injector->injectInto($stage->getAfterCallback())();
            }

            $this->manager->switch();

            return $this->run();
        }

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

    /**
     * @param T $data
     */
    private function validateData($data): bool
    {
        return ($data !== null
            && !$data instanceof ArtifactsStorage
            && !$data instanceof StageSwitcher);
    }
}
