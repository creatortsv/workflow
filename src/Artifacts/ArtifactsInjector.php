<?php

declare(strict_types=1);

namespace Creatortsv\Workflow\Artifacts;

use Closure;
use Creatortsv\Workflow\Utils\CallbackWrapper;
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

            if (class_exists($type)) {
                $artifacts = $this
                    ->getStorage()
                    ->useTypes()
                    ->get($type);

                if ($artifacts) {
                    $parameter->isVariadic()
                        ? array_push($parameters, ...$artifacts)
                        : array_push($parameters, $artifacts[max(array_keys($artifacts))]);

                    continue;
                }

                if ($type === ArtifactsStorage::class) {
                    $parameters[] = $this->storage;

                    continue;
                }
            }

            $parameters[] = null;
        }

        return fn () => $callback(...$parameters);
    }
}
