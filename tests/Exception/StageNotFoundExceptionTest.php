<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Tests\Exception;

use Creatortsv\WorkflowProcess\Exception\StagesNotFoundException;
use PHPUnit\Framework\TestCase;

class StageNotFoundExceptionTest extends TestCase
{
    public function test__construct(): void
    {
        $this->expectException(StagesNotFoundException::class);
        $this->expectErrorMessage('Stages "some" not found');

        throw new StagesNotFoundException('some');
    }
}
