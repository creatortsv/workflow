<?php

namespace Creatortsv\WorkflowProcess\Support;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD)]
class Stage
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly bool $enabled = true,
    ) {
    }
}
