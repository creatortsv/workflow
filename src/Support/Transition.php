<?php

namespace Creatortsv\WorkflowProcess\Support;

use Attribute;
use Closure;
use Creatortsv\WorkflowProcess\Enum\SwitchTo;

#[Attribute(
    Attribute::TARGET_CLASS |
    Attribute::TARGET_FUNCTION |
    Attribute::TARGET_METHOD |
    Attribute::TARGET_PROPERTY |
    Attribute::IS_REPEATABLE
)]
class Transition
{
    public readonly Closure|string|null $callback;

    public function __construct(
        public readonly SwitchTo|string $to,
        public readonly ?string $from = null,
        public readonly ?bool $expression = null,
        callable|string|null $callback = null,
    ) {
        $this->callback = is_callable($callback)
            ? $callback(...)
            : $callback;
    }
}
