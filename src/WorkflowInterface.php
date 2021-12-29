<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess;

use ArrayIterator;
use Creatortsv\WorkflowProcess\Runner\WorkflowRunner;

interface WorkflowInterface
{
    public function setStages(callable ...$stages): WorkflowInterface;
    public function getStages(): ArrayIterator;
    public function makeRunner(): WorkflowRunner;
}
