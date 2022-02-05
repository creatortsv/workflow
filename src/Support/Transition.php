<?php

namespace Creatortsv\WorkflowProcess\Support;

use Attribute;
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
    public function __construct(
        public readonly SwitchTo|string $to,
        public readonly ?string $from = null,
    ) {
    }
}
