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
    /**
     * @var callable
     */
    private $original;
    private ReflectionFunction $callback;
    private string $name;
    private ?string $method = null;
    private ?string $class = null;
    private int $count = 0;
    private int $number = 1;

    /**
     * @param T ...$parameters
     * @return T
     */
    public function __invoke(...$parameters)
    {
        $this->count ++ ;

        return $this
            ->callback
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

    public function name(): string
    {
        return $this->toString();
    }

    public function getOriginal(): callable
    {
        return $this->original;
    }

    public function getReflection(): ReflectionFunction
    {
        return $this->callback;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function number(): int
    {
        return $this->number;
    }

    /**
     * @throws ReflectionException
     */
    public static function of(callable $callback, int $number = 1): CallbackWrapper
    {
        if ($callback instanceof CallbackWrapper) {
            $callback = $callback->getOriginal();
        }

        return new CallbackWrapper($callback, $number);
    }

    /**
     * @throws ReflectionException
     */
    private function __construct(callable $callback, int $number = 1)
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
        $this->callback = new ReflectionFunction(Closure::fromCallable($callback));
        $this->original = $callback;
        $this->number = $number;
    }
}
