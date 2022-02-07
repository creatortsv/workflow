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

    public function __construct(
        public readonly SwitchTo|string $to,
        public readonly ?string $from = null,
        callable|bool $condition = true,
    ) {
        $this->expression = !$condition instanceof Closure && !is_bool($condition)
            ? $condition(...)
            : $condition;
    }
}
