<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Artifacts;

use Closure;
use Creatortsv\WorkflowProcess\Utils\CallbackWrapper;
use ReflectionException;

class ArtifactsInjector
{
    private ArtifactsStorage $storage;

    public function __construct(ArtifactsStorage $storage)
    {
        $this->storage = $storage;
    }

    public function getStorage(): ArtifactsStorage
    {
        return $this->storage;
    }

    /**
     * @throws ReflectionException
     */
    public function injectInto(callable $callback): Closure
    {
        if (!$callback instanceof CallbackWrapper) {
            $callback = CallbackWrapper::of($callback);
        }

        $parameters = [];

        foreach ($callback->getReflection()->getParameters() as $parameter) {
            $type = $parameter
                ->getType()
                ->getName();

            if (class_exists($type) || interface_exists($type)) {
                $artifacts = $this
                    ->getStorage()
                    ->useTypes()
                    ->get($type);
            }

            if (isset($artifacts) && $artifacts) {
                $parameter->isVariadic()
                    ? array_push($parameters, ...$artifacts)
                    : array_push($parameters, $artifacts[max(array_keys($artifacts))]);
            } else {
                $parameters[] = null;
            }
        }

        return fn () => $callback(...$parameters);
    }
}
