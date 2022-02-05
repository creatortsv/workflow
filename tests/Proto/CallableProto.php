<?php

namespace Creatortsv\WorkflowProcess\Tests\Proto;

class CallableProto
{
    public function __invoke(): bool
    {
        return true;
    }

    public function method(): void {}
}
