<?php

namespace Creatortsv\WorkflowProcess\Support;

use Attribute;
use Creatortsv\WorkflowProcess\Exception\WorkflowRequirementsException;

#[Attribute(Attribute::TARGET_CLASS)]
class Workflow
{
    /**
     * @throws WorkflowRequirementsException
     */
    public function __construct(public readonly string $required)
    {
        if (!class_exists($required) && !interface_exists($required)) {
            throw new WorkflowRequirementsException($required);
        }
    }
}
