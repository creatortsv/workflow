<?php

namespace Creatortsv\WorkflowProcess\Transition;

use Closure;
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
        $transition = [];
        $attributes = AttributeReader::of(object: $of)->read(
            attribute: Support\Transition::class,
            flags: AttributeReader::INCLUDE_ROOT
            | AttributeReader::INCLUDE_METHODS
            | AttributeReader::INCLUDE_PROPERTIES,
        );

        foreach ($attributes as [$reflect, $attribs]) {
            $function = function (Support\Transition $attr) use ($of, $reflect): Transition {
                if ($attr?->callback !== null) {
                    if (is_callable($attr?->callback)) {
                        $expression = $attr?->callback instanceof Closure
                            ? ($attr?->callback)
                            : ($attr?->callback)(...);
                    } else {
                        $expression = $of->{$attr?->callback}(...);
                    }
                } elseif ($attr?->expression !== null) {
                    $expression = $attr?->expression;
                }

                $transitionArgs = [
                    $attr?->to,
                    $attr?->from,
                    $attr?->except,
                ];

                if (isset($expression)) {
                    return new Transition(...$transitionArgs, condition: $expression);
                }

                return new Transition(...$transitionArgs, condition: match ($reflect::class) {
                    ReflectionClass::class => $of(...),
                    ReflectionMethod::class => $of->{$reflect->getShortName()}(...),
                    ReflectionFunction::class => $reflect->getClosure(),
                    ReflectionProperty::class => fn (): bool => $reflect->getValue((object) $of),
                });
            };

            array_push($transition, ...array_map($function, $attribs));
        }

        return $transition;
    }
}
