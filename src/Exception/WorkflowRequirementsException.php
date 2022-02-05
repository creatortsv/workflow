<?php

namespace Creatortsv\WorkflowProcess\Exception;

use Exception;

class WorkflowRequirementsException extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf(
            'Neither class nor interface of the requirement with the given name "%s" do not exist',
            $name,
        ));
    }
}
