<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess;

use ArrayIterator;
use Creatortsv\WorkflowProcess\Runner\WorkflowRunner;

interface WorkflowInterface
{
    /**
     * @return array<callable>
     */
    public function getStages(): array;
    public function setStages(callable ...$stages): WorkflowInterface;
    public function makeRunner(): WorkflowRunner;
}
