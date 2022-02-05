<?php

namespace Creatortsv\WorkflowProcess\Stage;

use Creatortsv\WorkflowProcess\Support;
use Creatortsv\WorkflowProcess\Support\Helper\AttributeReader;
use Creatortsv\WorkflowProcess\Transition\Transition;
use Creatortsv\WorkflowProcess\Transition\TransitionFactory;
use ReflectionException;

class StageFactory
{
    /**
     * @throws ReflectionException
     */
    public static function create(callable $callable): Stage
    {
        $reader = AttributeReader::of($callable);

        return self::makeStage($callable, $reader)->setTransitions(
            ...self::makeTransitions($callable),
        );
    }

    /**
     * @throws ReflectionException
     */
    private static function makeStage(callable $callable, AttributeReader $reader): Stage
    {
        $stageAttribute = self::getSingleAttribute($reader, Support\Stage::class);
        $artifactAttrib = self::getSingleAttribute($reader, Support\Artifacts::class);
        $artifactsNames = $artifactAttrib?->names ?? [];

        return (new Stage(
            $callable,
            enabled: $stageAttribute?->enabled ?? true,
            name: $stageAttribute?->name,
        ))
            ->setArtifactNames(...$artifactsNames);
    }

    /**
     * @return Transition[]
     * @throws ReflectionException
     */
    private static function makeTransitions(callable $callable): array
    {
        return TransitionFactory::create($callable);
    }

    /**
     * @throws ReflectionException
     */
    private static function getSingleAttribute(AttributeReader $reader, string $attribute): ?object
    {
        $attributes = $reader->read($attribute);

        [, $attributes] = array_pop($attributes) ?? [null, []];

        return array_pop($attributes);
    }
}
