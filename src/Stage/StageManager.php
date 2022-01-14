<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Stage;

use ArrayIterator;
use Closure;
use Creatortsv\WorkflowProcess\Exception\StageNotFoundException;
use Creatortsv\WorkflowProcess\Utils\CallbackWrapper;
use OutOfBoundsException;
use ReflectionException;

class StageManager
{
    private ArrayIterator $stages;
    private Closure $prev;
    private Closure $next;

    /**
     * @throws ReflectionException
     */
    public function __construct(callable ...$stages)
    {
        $this->stages = new ArrayIterator();

        $numbers = [];

        foreach ($stages as $stage) {
            $name = CallbackWrapper::of($stage)->name();
            $numbers[$name] ??= 0;
            $numbers[$name] ++ ;

            $this->stages->append(CallbackWrapper::of($stage, $numbers[$name]));
        }

        $this->stages->rewind();

        $current = $this
            ->stages
            ->key();

        $this->prev = $this::switcher($this::position($current, - 1));
        $this->next = $this::switcher($this::position($current, + 1));
    }

    public function getStages(): ArrayIterator
    {
        return $this->stages;
    }

    /**
     * @throws StageNotFoundException
     */
    public function switchTo(string $name, int $number = 1): StageManager
    {
        $names = array_map(fn (CallbackWrapper $stage): string => (string) $stage, (array) $this->stages);
        $names = array_keys($names, $name, true);
        $index = $names[ -- $number] ?? null;

        if ($index === null) {
            throw new StageNotFoundException($name, $number);
        }

        $this->next = $this::switcher($index);

        return $this;
    }

    public function back(): StageManager
    {
        $this->next = $this->prev;

        return $this;
    }

    public function skip(int $length = 1): StageManager
    {
        if ($length < 0) {
            throw new OutOfBoundsException();
        }

        $predicted = $this
            ->predict($this->next)
            ->key();

        $this->next = $this::switcher($this::position($predicted, + $length));

        return $this;
    }

    public function stop(): StageManager
    {
        $this->next = $this::switcher();

        return $this;
    }

    public function switch(): void
    {
        ($this->prev = $this::switcher($this::position($this->stages->key())));
        ($this->next)($this->stages);
        ($this->next = $this::switcher($this::position($this->stages->key(), + 1)));
    }

    public function previous(): ?CallbackWrapper
    {
        return $this
            ->predict($this->prev)
            ->current();
    }

    public function next(): ?CallbackWrapper
    {
        return $this
            ->predict($this->next)
            ->current();
    }

    private function predict(Closure $of): ArrayIterator
    {
        $of($stages = $this->getClonedStages());

        return $stages;
    }

    private function getClonedStages(): ArrayIterator
    {
        $stages = clone $this->stages;

        $this::switcher()($stages, $this->stages->key());

        return $stages;
    }

    private static function switcher(?int $position = null): Closure
    {
        return function (ArrayIterator $stages, ?int $input = null) use ($position): void {
            $input ??= $position;

            try {
                if ($input !== null) {
                    $stages->seek($input);
                } else {
                    throw new OutOfBoundsException();
                }
            } catch (OutOfBoundsException $e){
                $stages->seek(max(array_keys((array) $stages)));
                $stages->next();
            }
        };
    }

    private static function position(?int $index = null, int $add = 0): ?int
    {
        $index !== null && ($index += $add);

        return $index;
    }
}
