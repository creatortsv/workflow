<?php

namespace Creatortsv\WorkflowProcess\Transition;

use Creatortsv\WorkflowProcess\Support\Helper\AttributeReader;
use Creatortsv\WorkflowProcess\Support;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

class TransitionFactory
{
    /**
     * @return Transition[]
     * @throws ReflectionException
     */
    public static function create(callable $of): array
    {
        $reader = AttributeReader::of(object: $of);
        $isStage = (bool) count($reader->read(Support\Stage::class));

        $transition = [];
        $attributes = $reader->read(
            attribute: Support\Transition::class,
            flags: AttributeReader::INCLUDE_ROOT
            | AttributeReader::INCLUDE_METHODS
            | AttributeReader::INCLUDE_PROPERTIES,
        );

        foreach ($attributes as [$reflect, $attribs]) {
            $function = fn (Support\Transition $attr): Transition => new Transition(
                $attr?->to,
                $attr?->from,
                condition: match ($reflect::class) {
                    ReflectionClass::class => $isStage ?: $of(...),
                    ReflectionMethod::class,
                    ReflectionFunction::class => $reflect->getClosure((object) $of),
                    ReflectionProperty::class => fn (): bool => $reflect->getValue((object) $of),
                },
            );

            array_push($transition, ...array_map($function, $attribs));
        }

        return $transition;
    }
}
