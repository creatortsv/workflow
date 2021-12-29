<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Exception;

use PHPUnit\Framework\TestCase;

class StageNotFoundExceptionTest extends TestCase
{
    public function test__construct(): void
    {
        $this->expectException(StageNotFoundException::class);
        $this->expectErrorMessage('Stage with the given name "some" not found');

        throw new StageNotFoundException('some');
    }
}
