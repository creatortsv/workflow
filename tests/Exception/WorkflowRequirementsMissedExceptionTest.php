<?php

namespace Creatortsv\WorkflowProcess\Tests\Exception;

use Creatortsv\WorkflowProcess\Exception\WorkflowRequirementsMissedException;
use Creatortsv\WorkflowProcess\Tests\Proto\Amount;
use Creatortsv\WorkflowProcess\Workflow;
use Creatortsv\WorkflowProcess\Support;
use PHPUnit\Framework\TestCase;

class WorkflowRequirementsMissedExceptionTest extends TestCase
{
    public function test__construct(): void
    {
        $workflow = new #[Support\Workflow(required: Amount::class)] class extends Workflow {};
        $exception = new WorkflowRequirementsMissedException($workflow);

        $this->assertSame('One of passed arguments must be instance of the "'.Amount::class.'"', $exception->getMessage());
    }
}
