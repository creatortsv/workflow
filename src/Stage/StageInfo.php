<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Stage;

use Creatortsv\WorkflowProcess\Utils\CallbackWrapper;

class StageInfo
{
    private CallbackWrapper $wrapper;

    public function name(): string
    {
        return $this
            ->wrapper
            ->name();
    }

    public function getExecutedTimes(): int
    {
        return $this
            ->wrapper
            ->getCount();
    }

    public function number(): int
    {
        return $this
            ->wrapper
            ->number();
    }

    public function is(string $name): bool
    {
        return $this->name() === $name;
    }

    public static function of(?CallbackWrapper $wrapper = null): ?StageInfo
    {
        if ($wrapper !== null) {
            return new self($wrapper);
        }

        return null;
    }

    private function __construct(CallbackWrapper $wrapper)
    {
        $this->wrapper = $wrapper;
    }
}
