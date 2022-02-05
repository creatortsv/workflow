<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess;

use Creatortsv\WorkflowProcess\Runner\WorkflowRunner;

interface WorkflowInterface
{
    public function makeRunner(): WorkflowRunner;
}
