<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Artifacts;

use Closure;
use Creatortsv\WorkflowProcess\Support\Helper\CallableInstance;
use ReflectionException;

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
            $type = $parameter->getType()->getName();

            if ($this->excluded($name, $type, ...$exclude) !== true) {
                $artifacts = $this
                    ->storage
                    ->get(
                        nameOrType: class_exists($type) || interface_exists($type)
                            ? $type
                            : $name,
                    );

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

    private function excluded(string $name, string $type, string ...$exclude): bool
    {
        if (in_array($type, $exclude, true) ||
            in_array($name, $exclude, true)) {
            return true;
        }

        foreach ($exclude as $nameOrType) {
            if (is_subclass_of($type, $nameOrType) ||
                in_array($nameOrType, class_implements($type), true)) {
                return true;
            }
        }

        return false;
    }
}
