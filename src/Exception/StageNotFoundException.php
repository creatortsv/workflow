<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Exception;

use OutOfBoundsException;

class StageNotFoundException extends OutOfBoundsException
{
    public function __construct(string $name, int $number)
    {
        parent::__construct(
            sprintf(
                'Stage with the given name "%s" and the number "%s" not found',
                $name,
                $number,
            ),
        );
    }
}
