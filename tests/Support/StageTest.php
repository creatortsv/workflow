<?php

namespace Creatortsv\WorkflowProcess\Tests\Support;

use Creatortsv\WorkflowProcess\Support\Stage;
use ReflectionException;

class StageTest extends AbstractAttributeTestCase
{
    /**
     * @throws ReflectionException
     */
    public function test__construct(): void
    {
        $object = new #[Stage] class {};
        $attrib = $this->getAttribute($object, Stage::class);

        $this->assertInstanceOf(Stage::class, $attrib);
        $this->assertTrue($attrib->enabled);
        $this->assertNull($attrib->name);

        $object = new #[Stage(name: 'some')] class {};
        $attrib = $this->getAttribute($object, Stage::class);

        $this->assertInstanceOf(Stage::class, $attrib);
        $this->assertTrue($attrib->enabled);
        $this->assertSame('some', $attrib->name);

        $object = new #[Stage(name: 'some', enabled: false)] class {};
        $attrib = $this->getAttribute($object, Stage::class);

        $this->assertInstanceOf(Stage::class, $attrib);
        $this->assertFalse($attrib->enabled);
        $this->assertSame('some', $attrib->name);
    }
}
