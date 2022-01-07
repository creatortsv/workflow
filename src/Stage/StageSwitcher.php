<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Stage;

use ArrayIterator;
use Closure;
use Creatortsv\WorkflowProcess\Exception\StageNotFoundException;
use Creatortsv\WorkflowProcess\Utils\CallbackWrapper;

class StageSwitcher
{
    /**
     * The stage list
     *
     * @var ArrayIterator<CallbackWrapper>
     */
    private ArrayIterator $stages;

    /**
     * Callback which will be executed
     * to move the position of the stage list
     * to the next stage
     */
    private Closure $next;

    /**
     * Callback which will be executed
     * to move the position of the stage list
     * to the previous stage
     */
    private Closure $prev;

    public function __construct(ArrayIterator $stages)
    {
        $this->stages = $stages;
        $this->stages->rewind();

        $this->next = $this->nextCallback();
        $this->prev = $this->prevCallback();
    }

    public function __invoke(string $name, ?int $number = 1): void
    {
        $this->switchTo($name, $number);
    }

    public function switchTo(string $name, ?int $number = 1): void
    {
        $this->next = $this->switchCallback($name, $number);
    }

    public function switch(): void
    {
        ($this->prev = $this->prevCallback($this->stages->key()));
        ($this->next)($this->stages);
        ($this->next = $this->nextCallback());
    }

    public function prev(): ?StageInfo
    {
        ($this->prev)($stages = $this->getClonedStages());
        $stage = $stages->current();

        return !$stage ? $stage : new StageInfo($stage);
    }

    public function next(): ?StageInfo
    {
        ($this->next)($stages = $this->getClonedStages());
        $stage = $stages->current();

        return !$stage ? $stage : new StageInfo($stage);
    }

    private function nextCallback(): Closure
    {
        return fn (ArrayIterator $stages) => $stages->next();
    }

    private function prevCallback(?int $position = null): Closure
    {
        return function (ArrayIterator $stages) use ($position): void {
            $position ??= $stages->key() - 1;
            $position > -1 && $stages->seek($position);

            if ($position < 0) {
            /** Should return NULL when it is possible only at the outside of the range */
                $stages->seek($stages->count() - 1);
                $stages->next();
            }
        };
    }

    private function switchCallback(string $name, int $number): Closure
    {
        return function (ArrayIterator $stages) use ($name, $number): void {
            $names = array_map(fn (CallbackWrapper $stage): string => (string) $stage, (array) $stages);
            $names = array_keys($names, $name, true);
            $index = $names[$number - 1] ?? null;
            $index !== null && $stages->seek($index);

            if ($index === null) {
                throw new StageNotFoundException($name, $number);
            }
        };
    }

    private function getClonedStages(): ArrayIterator
    {
        $stages = clone $this->stages;
        $stages->seek($this->stages->key());

        return $stages;
    }
}
