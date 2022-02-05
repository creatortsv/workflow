<?php

namespace Creatortsv\WorkflowProcess\Exception;

use Creatortsv\WorkflowProcess\Workflow;
use Exception;

class WorkflowRequirementsMissedException extends Exception
{
    public function __construct(Workflow $workflow)
    {
        parent::__construct(
            sprintf('One of passed arguments must be instance of the "%s"', $workflow->required),
        );
    }
}
