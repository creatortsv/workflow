<?php

namespace Creatortsv\WorkflowProcess\Support\Helper;

use Closure;
use ReflectionException;
use ReflectionFunction;

final class CallableInstance
{
    public readonly Closure $func;
    public readonly string $name;
    public readonly ?string $method;
    public readonly ?string $class;

    private int $count = 0;

    /**
     * @var callable
     */
    private $original;

    public function __construct(callable $callable)
    {/* Get the right name of the callable */
        is_callable($callable, true, $name);

        $index = strrpos($name, '::');

        if ($index !== false) {
            $this->class = substr($name, 0, $index);
            $this->method = substr($name, $index + 2);
            $this->method === '__invoke' && ($name = $this->class);
        }

        $this->name = $name;
        $this->func = $callable instanceof Closure
            ? $callable
            : $callable(...);

        $this->original = $callable;
    }

    public function __invoke(mixed ...$arguments): mixed
    {
        $result = ($this->func)(...$arguments);

        $this->count ++ ;

        return $result;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getOriginal(): callable
    {
        return $this->original;
    }

    /**
     * @throws ReflectionException
     */
    public function reflect(): ReflectionFunction
    {
        return new ReflectionFunction($this->func);
    }
}
