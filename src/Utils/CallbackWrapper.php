<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Utils;

use Closure;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use function Symfony\Component\String\u;

/**
 * @template T
 */
class CallbackWrapper
{
    private ReflectionFunction $stage;
    private string $name;
    private string $method;
    private ?string $class;

    /**
     * @throws ReflectionException
     */
    public function __construct(callable $stage)
    {
        if (!is_callable($stage, false, $name)) {
            throw new InvalidArgumentException(
                sprintf('Stage with the given name "%s" must be callable', $name),
            );
        }

        $this->name = $name;
        $this->method = $name;

        $index = strrpos($name, '::');

        if ($index !==false) {
            $this->class = substr($name, 0, $index);
            $this->method = substr($name, $index + 1);
        }

        $this->stage = new ReflectionFunction(Closure::fromCallable($stage));
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
        return $this->class ?? $this->name;
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
    public static function of(callable $stage): CallbackWrapper
    {
        return new CallbackWrapper($stage);
    }
}
