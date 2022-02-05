<?php

namespace Creatortsv\WorkflowProcess\Stage;

use ArrayIterator;
use Creatortsv\WorkflowProcess\Enum\SwitchTo;
use Creatortsv\WorkflowProcess\Exception\StagesNotFoundException;
use OutOfBoundsException;

final class StageManager
{
    public readonly ArrayIterator $stages;
    private ?int $next;
    private ?int $prev = null;
    private bool $blocked = false;

    public function __construct(Stage ...$stages)
    {
        $this->stages = new ArrayIterator($stages);
        $currentIndex = (int) $this->stages->key();

        $this->next = $this
            ->resolve( ++ $currentIndex, predict: true)
            ->key();
    }

    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    /**
     * @throws StagesNotFoundException
     */
    public function switchTo(SwitchTo|string $where): StageManager
    {
        if ($where instanceof SwitchTo) {
            $this->next = match ($where) {
                SwitchTo::END => null,
                SwitchTo::BACK => $this->resolve($this->prev ?? 0, predict: true)->key(),
                SwitchTo::REPEAT => $this->stages->key(),
                SwitchTo::RETURN => $this->resolve($this->prev ?? 0, predict: true)->key() + 1,
                SwitchTo::START => $this->resolve(0, predict: true)->key(),
            };
        } else {
            $this->next = $this->search($where);
        }

        $this->blocked = true;

        return $this;
    }

    public function switch(): void
    {
        $this->prev = $this->stages->key();
        $this->resolve($this->next);

        if ($this->next !== null) {
            $predicted = $this->resolve($this->next, predict: true);
            $predicted->next();

            $this->next = $predicted->key();
        }

        $this->blocked = false;
    }

    public function previous(): ?Stage
    {
        return $this
            ->resolve($this->prev, predict: true)
            ->current();
    }

    public function next(): ?Stage
    {
        return $this
            ->resolve($this->next, predict: true)
            ->current();
    }

    private function resolve(int $index = null, bool $predict = false): ArrayIterator
    {
        $stages = $predict ? clone $this->stages : $this->stages;
        $length = $stages->count();

        if ($length) {
            try {
                $stages->seek($index ?? -1);
            } catch (OutOfBoundsException) {
                $stages->seek(--$length);
                $stages->next();
            }
        }

        return $stages;
    }

    /**
     * @throws StagesNotFoundException
     */
    private function search(string $name): int
    {
        $names = array_map(fn (Stage $stage): string => $stage->name, (array) $this->stages);
        $index = array_keys($names, $name, true);

        return $index[0] ?? throw new StagesNotFoundException($name);
    }
}
