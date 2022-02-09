<?php

namespace Creatortsv\WorkflowProcess\Tests\Transition;

use Closure;
use Creatortsv\WorkflowProcess\Support;
use Creatortsv\WorkflowProcess\Transition\Transition;
use Creatortsv\WorkflowProcess\Transition\TransitionFactory;
use Iterator;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class TransitionFactoryTest extends TestCase
{
    public function data(): Iterator
    {
        yield 'Stage as callable object with all type of artifacts' => [
            new #[
                Support\Stage,
                Support\Transition('stage.1', expression: true),
                Support\Transition('stage.2', callback: '__invoke'),
                Support\Transition('stage.3'),
            ] class {
                #[Support\Transition('stage.4', 'stage.1')]
                #[Support\Transition('stage.5', 'stage.2')]
                public bool $done = false;

                #[Support\Transition('stage.6')]
                public function toSecond(): bool
                {
                    return $this->done;
                }

                public function __invoke(): bool
                {
                    return true;
                }
            },
        ];
    }

    /**
     * @dataProvider data
     * @throws ReflectionException
     */
    public function testCreate(callable|object $callable): void
    {
        $transitions = TransitionFactory::create($callable);

        $this->assertCount(6, $transitions);

        foreach ($transitions as $i => $transition) {
            $this->assertInstanceOf(Transition::class, $transition);
            $this->assertSame('stage.' . $i + 1, $transition->to);

            switch ($i) {
                case 0:
                    $this->assertTrue($transition->expression);

                    break;
                case 1:
                case 2:
                    $this->assertInstanceOf(Closure::class, $transition->expression);

                    break;
                case 3:
                case 4:
                case 5:
                    $this->assertInstanceOf(Closure::class, $transition->expression);
                    $this->assertFalse(($transition->expression)());
            }
        }

        $callable->done = true;

        array_walk($transitions, fn (Transition $transition)
            => $this->assertTrue(
                $transition->expression instanceof Closure
                ? ($transition->expression)()
                : ($transition->expression),
            ));
    }
}
