<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Stage;

use Creatortsv\WorkflowProcess\Utils\CallbackWrapper;

class StageInfo
{
    private string $name;
    private int $count;
    private int $number;

    public function __construct(CallbackWrapper $wrapper)
    {
        $this->name = $wrapper->name();
        $this->count = $wrapper->getCount();
        $this->number = $wrapper->number();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function getExecutedTimes(): int
    {
        return $this->count;
    }

    public function number(): ?int
    {
        return $this->number;
    }
}
