<?php

namespace Creatortsv\WorkflowProcess\Tests\Transition;

use Closure;
use Creatortsv\WorkflowProcess\Tests\Proto\CallableProto;
use Creatortsv\WorkflowProcess\Transition\Transition;
use PHPUnit\Framework\TestCase;

class TransitionTest extends TestCase
{
    public function test__construct(): void
    {
        $transition = new Transition('some');

        $this->assertTrue($transition->expression);
        $this->assertEmpty($transition->from);
        $this->assertSame('some', $transition->to);

        $transition = new Transition('some', 'from', condition: new CallableProto());

        $this->assertInstanceOf(Closure::class, $transition->expression);
        $this->assertTrue(($transition->expression)());
        $this->assertSame('some', $transition->to);
        $this->assertSame(['from'], $transition->from);

        $transition = new Transition('some', 'from', 'from', new CallableProto());

        $this->assertInstanceOf(Closure::class, $transition->expression);
        $this->assertTrue(($transition->expression)());
        $this->assertSame('some', $transition->to);
        $this->assertEmpty($transition->from);
    }
}
