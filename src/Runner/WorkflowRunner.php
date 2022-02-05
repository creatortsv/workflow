<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Runner;

use Creatortsv\WorkflowProcess\Artifacts\ArtifactsInjector;
use Creatortsv\WorkflowProcess\Artifacts\ArtifactsStorage;
use Creatortsv\WorkflowProcess\Exception\StagesNotFoundException;
use Creatortsv\WorkflowProcess\Stage\Stage;
use Creatortsv\WorkflowProcess\Stage\StageManager;
use Creatortsv\WorkflowProcess\Stage\StageSwitcher;
use Creatortsv\WorkflowProcess\Transition\Transition;
use ReflectionException;

final class WorkflowRunner
{
    private StageManager $manager;
    private ArtifactsInjector $injector;
    private bool $started = false;

    /**
     * @param array<int, mixed> $context
     */
    public function __construct(array $context, Stage ...$stages)
    {
        $storage = new ArtifactsStorage();

        $this->manager = new StageManager(...$stages);
        $this->injector = new ArtifactsInjector($storage);

        $context = [new StageSwitcher($this->manager), $storage, ...array_values($context)];

        array_walk($context, fn ($artifact) => $storage->set($artifact));
    }

    /**
     * @throws ReflectionException
     * @throws StagesNotFoundException
     */
    public function run(): WorkflowRunner
    {
        if ($this->started) {
            $this->manager->switch();
        } else {
            $this->started = true;
        }

        $stage = $this
            ->manager
            ->stages
            ->current();

        if ($stage instanceof Stage) {
            $this->execute($stage);

            if ($this->manager->isBlocked() !== true) {
                $previous = $this
                    ->manager
                    ->previous();

                $transition = $this->findTransition($stage, $previous?->name);
                $transition !== null && $this->manager->switchTo($transition->to);
            }

            return $this->run();
        }

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function then(callable $callback): mixed
    {
        return $this
            ->injector
            ->injectInto($callback)();
    }

    /**
     * @throws ReflectionException
     */
    private function execute(Stage $stage): void
    {
        $data = $this
            ->injector
            ->injectInto($stage->instance)();

        if ($data === null) {
            return;
        }

        $data = (array) $data;

        if (!array_is_list($data)) {
            $this->injector
                ->storage
                ->set($data, current($stage->artifactNames) ?: null);

            return;
        }

        array_walk($data, fn ($artifact, int $i) => $this->injector
            ->storage
            ->set($artifact, $stage->artifactNames[$i] ?? null));
    }

    /**
     * @throws ReflectionException
     */
    private function findTransition(Stage $stage, ?string $from = null): ?Transition
    {/* Transition from has high priority
        1. Match all of named transitions
        2. Match all of nullable transitions
        3. Switch to the next */
        foreach ($stage->getTransitions($from) as $transition) {
            if ($transition->expression === true || $this
                ->injector
                ->injectInto(
                    $transition->expression, StageSwitcher::class, ArtifactsStorage::class,
                )() === true) {
                return $transition;
            }
        }

        if ($from !== null) {
            return $this->findTransition($stage);
        }

        return null;
    }
}
