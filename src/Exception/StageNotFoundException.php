<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Exception;

use Exception;
use Throwable;

class StageNotFoundException extends Exception
{
    public function __construct(string $name, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Stage with the given name "%s" not found', $name),
            $code,
            $previous,
        );
    }
}
