<?php

declare(strict_types=1);

namespace Creatortsv\Workflow;

use ArrayIterator;
use Creatortsv\Workflow\Runner\WorkflowRunner;

interface WorkflowInterface
{
    public function setStages(callable ...$stages): WorkflowInterface;
    public function getStages(): ArrayIterator;
    public function makeRunner(): WorkflowRunner;
}
