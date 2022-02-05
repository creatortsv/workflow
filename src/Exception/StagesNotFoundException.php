<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Exception;

use Exception;

class StagesNotFoundException extends Exception
{
    public function __construct(string ...$name)
    {
        parent::__construct(
            sprintf('Stages "%s" not found', implode(',', $name)),
        );
    }
}
