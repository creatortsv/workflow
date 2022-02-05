<?php

namespace Creatortsv\WorkflowProcess\Tests\Proto;

class Amount
{
    public function __construct(public int $amount = 0) {}
    public function add(int $plus): Amount
    {
        $this->amount += $plus;

        return $this;
    }
}
