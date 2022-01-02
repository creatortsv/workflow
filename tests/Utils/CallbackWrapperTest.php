<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Tests\Utils;

use Closure;
use Creatortsv\WorkflowProcess\Tests\Proto\TestIncrementStage;
use Creatortsv\WorkflowProcess\Utils\CallbackWrapper;
use Iterator;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class CallbackWrapperTest extends TestCase
{
    public function provider(): Iterator
    {
        yield 'Closure' => [fn (): bool => true, Closure::class];
        yield 'Invokable' => [new TestIncrementStage(), TestIncrementStage::class];
        yield 'Array' => [[new TestIncrementStage(), 'some'], TestIncrementStage::class . '::some'];
    }

    /**
     * @dataProvider provider
     * @throws ReflectionException
     */
    public function testOf(callable $callback, string $name): void
    {
        $wrapper = CallbackWrapper::of($callback);

        $this->assertInstanceOf(CallbackWrapper::class, $wrapper);
        $this->assertSame($name, $wrapper->toString());
    }
}
