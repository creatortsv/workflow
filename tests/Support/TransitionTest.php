<?php

namespace Creatortsv\WorkflowProcess\Tests\Support;

use Creatortsv\WorkflowProcess\Support\Transition;
use ReflectionException;

class TransitionTest extends AbstractAttributeTestCase
{
    /**
     * @throws ReflectionException
     */
    public function test__construct(): void
    {
        $object = new #[Transition(to: 'some')] class {};
        $attrib = $this->getAttribute($object, Transition::class);

        $this->assertInstanceOf(Transition::class, $attrib);
        $this->assertSame('some', $attrib->to);
        $this->assertNull($attrib->from);

        $object = new #[Transition(to: 'some', from: 'other')] class {};
        $attrib = $this->getAttribute($object, Transition::class);

        $this->assertInstanceOf(Transition::class, $attrib);
        $this->assertSame('some', $attrib->to);
        $this->assertSame('other', $attrib->from);
    }
}
