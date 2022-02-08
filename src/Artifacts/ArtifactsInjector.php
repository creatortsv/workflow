<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Artifacts;

use Closure;
use Creatortsv\WorkflowProcess\Support\Helper\CallableInstance;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

class ArtifactsInjector
{
    public function __construct(public readonly ArtifactsStorage $storage)
    {
    }

    /**
     * @throws ReflectionException
     */
    public function injectInto(callable $callback, string ...$exclude): Closure
    {
        !$callback instanceof CallableInstance &&
        ($callback = new CallableInstance($callback));

        $parameters = [];

        foreach ($callback
            ->reflect()
            ->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (!in_array($name, $exclude, true) && $this::excluded($type, ...$exclude) !== true) {
                $artifacts = $this->findArguments($name, $type);

                if ($artifacts) {
                    $parameter->isVariadic()
                        ? array_push($parameters, ...$artifacts)
                        : array_push($parameters, array_pop($artifacts));

                    continue;
                }
            }

            !$parameter->isVariadic() && ($parameters[] = null);
        }

        return fn () => $callback(...$parameters);
    }

    private function findArguments(?string $param = null, ?ReflectionType $type = null): array
    {
        if ($type instanceof ReflectionIntersectionType) {
            $types = $type->getTypes();
            $count = count($types);
            $names = array_fill(0, $count, null);
            $items = array_unique(array_merge(
                ...array_map($this->findArguments(...), $names, $types),
            ), SORT_REGULAR);

            return array_filter($items, fn ($arg): bool
                => count(array_filter($types, fn (ReflectionNamedType $type): bool
                => is_subclass_of($arg, $type->getName()))) === $count);
        }

        if ($type instanceof ReflectionUnionType) {
            $types = $type->getTypes();
            $names = array_fill(0, count($types), $param);

            return array_merge(
                ...array_map($this->findArguments(...), $names, $types),
            );
        }

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $this
                ->storage
                ->get(nameOrType: $type->getName());
        }

        if ($param) {
            return $this
                ->storage
                ->get(nameOrType: $param);
        }

        return [];
    }

    private static function excluded(?ReflectionType $type = null, string ...$exclude): bool
    {
        if ($type instanceof ReflectionIntersectionType ||
            $type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $t) {
                if (self::excluded($t, ...$exclude)) {
                    return true;
                }
            }
        } elseif ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            foreach ($exclude as $item) {
                if (is_subclass_of($type->getName(), $item)) {
                    return true;
                }
            }
        }

        return false;
    }
}
