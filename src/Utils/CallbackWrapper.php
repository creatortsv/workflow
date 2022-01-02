<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Utils;

use Closure;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;

/**
 * @template T
 */
class CallbackWrapper
{
    private ReflectionFunction $stage;
    private string $name;
    private ?string $method = null;
    private ?string $class = null;

    /**
     * @throws ReflectionException
     */
    public function __construct(callable $callback)
    {
        if (!is_callable($callback, false, $name)) {
            throw new InvalidArgumentException(
                sprintf('Stage with the given name "%s" must be callable', $name),
            );
        }

        $index = strrpos($name, '::');

        if ($index !== false) {
            $this->class = substr($name, 0, $index);

            $method = substr($name, $index + 2);
            $method !== '__invoke' && ($this->method = $method);
        }

        $this->name = $name;
        $this->stage = new ReflectionFunction(Closure::fromCallable($callback));
    }

    /**
     * @return T
     */
    public function __invoke(?object ...$parameters)
    {
        return $this
            ->stage
            ->invoke(...$parameters);
    }

    public function __toString(): string
    {
        return $this->method !== null
            ? $this->name
            : $this->class;
    }

    public function toString(): string
    {
        return (string) $this;
    }

    public function getReflection(): ReflectionFunction
    {
        return $this->stage;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @throws ReflectionException
     */
    public static function of(callable $callback): CallbackWrapper
    {
        return new CallbackWrapper($callback);
    }
}
