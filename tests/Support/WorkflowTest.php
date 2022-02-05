<?php

namespace Creatortsv\WorkflowProcess\Tests\Support;

use Creatortsv\WorkflowProcess\Exception\WorkflowRequirementsException;
use Creatortsv\WorkflowProcess\Support\Workflow;
use Creatortsv\WorkflowProcess\Tests\Proto\Amount;
use Exception;
use ReflectionException;

class WorkflowTest extends AbstractAttributeTestCase
{
    /**
     * @throws ReflectionException
     */
    public function test__construct(): void
    {
        $object = new #[Workflow(required: Amount::class)] class {};
        $attrib = $this->getAttribute($object, Workflow::class);

        $this->assertInstanceOf(Workflow::class, $attrib);
        $this->assertSame(Amount::class, $attrib->required);

        try {
            $this->getAttribute(new #[Workflow(required: 'some')] class {}, Workflow::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(WorkflowRequirementsException::class, $e);
            $this->assertSame(
                'Neither class nor interface of the requirement with the given name "some" do not exist',
                $e->getMessage(),
            );
        }
    }
}
