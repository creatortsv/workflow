<?php

namespace Creatortsv\WorkflowProcess\Tests\Support\Helper;

use Closure;
use Creatortsv\WorkflowProcess\Support\Helper\CallableInstance;
use Creatortsv\WorkflowProcess\Tests\Proto\CallableProto;
use Iterator;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionFunction;

class CallableInstanceTest extends TestCase
{
    public function data(): Iterator
    {
        yield 'Object' => [new CallableProto()];
        yield 'Closure' => [fn () => true];
        yield 'Callable array' => [[new CallableProto(), 'method']];
    }

    /**
     * @dataProvider data
     */
    public function testInstance(callable $callable): CallableInstance
    {
        $instance = new CallableInstance($callable);

        $this->assertInstanceOf(Closure::class, $instance->func);
        $this->assertSame(0, $instance->getCount());

        is_array($callable)
            ? $this->assertSame('method', $instance->method)
            : $this->assertSame('__invoke', $instance->method);

        is_array($callable) || $callable instanceof CallableProto
            ? $this->assertSame(CallableProto::class, $instance->class)
            : $this->assertSame(Closure::class, $instance->class);

        match (true) {
            is_array($callable) => $this->assertSame(CallableProto::class . '::method', $instance->name),
            $callable instanceof CallableProto => $this->assertSame(CallableProto::class, $instance->name),
            $callable instanceof Closure => $this->assertSame(Closure::class, $instance->name),
        };

        match (true) {
            is_array($callable) => $this->assertIsArray($instance->getOriginal()),
            $callable instanceof CallableProto => $this->assertInstanceOf(CallableProto::class, $instance->getOriginal()),
            $callable instanceof Closure => $this->assertInstanceOf(Closure::class, $instance->getOriginal()),
        };

        $instance();

        $this->assertSame(1, $instance->getCount());

        return $instance;
    }

    /**
     * @dataProvider data
     * @throws ReflectionException
     */
    public function testReflect(callable $instance): void
    {
        $instance = new CallableInstance($instance);

        $this->assertInstanceOf(ReflectionFunction::class, $instance->reflect());
    }
}
