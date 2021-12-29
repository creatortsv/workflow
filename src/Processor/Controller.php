<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Processor;

use ArrayIterator;
use Creatortsv\WorkflowProcess\Exception\StageNotFoundException;
use Creatortsv\WorkflowProcess\Utils\CallbackWrapper;

class Controller
{
    private ArrayIterator $stages;

    public function __construct(CallbackWrapper ...$stages)
    {
        $this->stages = new ArrayIterator($stages);
    }

    /**
     * @throws StageNotFoundException
     */
    public function __invoke(string $name): Controller
    {
        $names = array_map(fn (CallbackWrapper $stage): string => (string) $stage, $this
            ->stages
            ->getArrayCopy());

        $index = array_search($name, $names, true);

        if ($index !== false) {
            $this->stages->seek($index);
        } else {
            throw new StageNotFoundException($name);
        }

        return $this;
    }

    public function stage(): ?CallbackWrapper
    {
        return $this
            ->stages
            ->current();
    }

    public function next(): void
    {
        if ($this->stages->valid()) {
            $this->stages->next();
        }
    }
}
