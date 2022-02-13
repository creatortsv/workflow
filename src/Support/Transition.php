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

    public readonly array $from;
    public readonly array $except;

    public function __construct(
        public readonly SwitchTo|string $to,
        string|array|null $from = [],
        string|array|null $except = [],
        public readonly ?bool $expression = null,
        callable|string|null $callback = null,
    ) {
        if ($from && is_string($from)) {
            $from = [$from];
        }

        if ($except && is_string($except)) {
            $except = [$except];
        }

        $this->from = array_diff($from, $except);
        $this->except = $except;
        $this->callback = is_callable($callback)
            ? $callback(...)
            : $callback;
    }
}
