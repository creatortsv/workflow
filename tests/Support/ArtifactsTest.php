<?php

namespace Creatortsv\WorkflowProcess\Tests\Support;

use Creatortsv\WorkflowProcess\Support\Artifacts;
use Creatortsv\WorkflowProcess\Support\Helper\AttributeReader;
use ReflectionException;

class ArtifactsTest extends AbstractAttributeTestCase
{
    /**
     * @throws ReflectionException
     */
    public function test__construct(): void
    {
        $object = new #[Artifacts('one', 'two')] class {};

        $attribute = $this->getAttribute($object, Artifacts::class);

        $this->assertInstanceOf(Artifacts::class, $attribute);
        $this->assertSame('one', $attribute->names[0]);
        $this->assertSame('two', $attribute->names[1]);
    }
}
