<?php

namespace Creatortsv\WorkflowProcess\Support\Helper;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

final class SpyHacker
{
    private readonly ReflectionClass $reflection;

    public static function hack(object $object): SpyHacker
    {
        return new SpyHacker(instance: $object);
    }

    private function __construct(private readonly object $instance)
    {
        $this->reflection = new ReflectionClass($this->instance);
    }

    public function __get(string $name): mixed
    {
        try {
            return $this->getProperty($name)->getValue($this->instance);
        } catch (ReflectionException) {
            return $this->instance->{__FUNCTION__}($name);
        }
    }

    public function __set(string $name, mixed $value): void
    {
        try {
            $this->getProperty($name)->setValue($this->instance, $value);
        } catch (ReflectionException) {
            $this->instance->{__FUNCTION__}($name, $value);
        }
    }

    public function __call(string $name, array $arguments): mixed
    {
        try {
            return $this->getMethod($name)->invoke($this->instance, ...$arguments);
        } catch (ReflectionException) {
            return $this->instance->__call($name, $arguments);
        }
    }

    /**
     * @throws ReflectionException
     */
    private function getProperty(string $name): ReflectionProperty
    {
        $property = $this
            ->reflection
            ->getProperty($name);

        if ($property->isPublic() !== true) {
            $property->setAccessible(true);
        }

        return $property;
    }

    /**
     * @throws ReflectionException
     */
    private function getMethod(string $name): ReflectionMethod
    {
        $method = $this
            ->reflection
            ->getMethod($name);

        if ($method->isConstructor() ||
            $method->isDestructor()) {
            throw new ReflectionException();
        }

        if ($method->isPublic() !== true) {
            $method->setAccessible(true);
        }

        return $method;
    }
}
