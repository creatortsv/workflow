<?php

namespace Creatortsv\WorkflowProcess\Tests\Support;

use Creatortsv\WorkflowProcess\Support\Transition;
use Creatortsv\WorkflowProcess\Tests\Proto\CallableProto;
use ReflectionException;

class TransitionTest extends AbstractAttributeTestCase
{
    /**
     * @throws ReflectionException
     */
    public function test__construct(): void
    {
        $object = new #[Transition(to: 'some', callback: 'method')] class {
            public function method(): void {}
        };

        $attrib = $this->getAttribute($object, Transition::class);

        $this->assertInstanceOf(Transition::class, $attrib);
        $this->assertSame('some', $attrib->to);
        $this->assertEmpty($attrib->from);
        $this->assertNull($attrib->expression);
        $this->assertSame('method', $attrib->callback);

        $object = new #[Transition(to: 'some', from: 'other', except: 'other', expression: true)] class {};
        $attrib = $this->getAttribute($object, Transition::class);

        $this->assertInstanceOf(Transition::class, $attrib);
        $this->assertSame('some', $attrib->to);
        $this->assertSame([], $attrib->from);
        $this->assertTrue($attrib->expression);
        $this->assertNull($attrib->callback);

        $object = new #[Transition(to: 'some', callback: [CallableProto::class, 'staticMethod'])] class {};
        $attrib = $this->getAttribute($object, Transition::class);

        $this->assertInstanceOf(Transition::class, $attrib);
        $this->assertSame('some', $attrib->to);
        $this->assertEmpty($attrib->from);
        $this->assertNull($attrib->expression);
        $this->assertTrue(is_callable($attrib->callback));
    }
}
