<?php

namespace Creatortsv\WorkflowProcess\Transition;

use Closure;
use Creatortsv\WorkflowProcess\Enum\SwitchTo;

/**
 * Transition describes which stage will be next
 */
final class Transition
{
    /**
     * If transition's expression returns TRUE
     * that transition will be used next
     */
    public readonly Closure|bool $expression;
    public readonly array $from;
    public readonly array $except;

    public function __construct(
        public readonly SwitchTo|string $to,
        string|array|null $from = [],
        string|array|null $except = [],
        callable|bool $condition = true,
    ) {
        if ($from && is_string($from)) {
            $from = [$from];
        }

        if ($except && is_string($except)) {
            $except = [$except];
        }

        $this->from = array_diff($from, $except);
        $this->except = $except;
        $this->expression = !$condition instanceof Closure && !is_bool($condition)
            ? $condition(...)
            : $condition;
    }
}
