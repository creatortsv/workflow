<?php

namespace Creatortsv\WorkflowProcess\Support;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD)]
class Artifacts
{
    public readonly array $names;
    public function __construct(string $name, string ...$names)
    {
        $this->names = [$name, ...$names];
    }
}
