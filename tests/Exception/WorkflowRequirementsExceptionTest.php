<?php

namespace Creatortsv\WorkflowProcess\Tests\Exception;

use Creatortsv\WorkflowProcess\Exception\WorkflowRequirementsException;
use PHPUnit\Framework\TestCase;

class WorkflowRequirementsExceptionTest extends TestCase
{
    public function test__construct(): void
    {
        $exception = new WorkflowRequirementsException('some');

        $this->assertSame('Neither class nor interface of the requirement with the given name "some" do not exist', $exception->getMessage());
    }
}
