<?php

namespace Creatortsv\WorkflowProcess\Tests\Stage;

use Closure;
use Creatortsv\WorkflowProcess\Stage\Stage;
use Creatortsv\WorkflowProcess\Support\Helper\CallableInstance;
use Creatortsv\WorkflowProcess\Transition\Transition;
use PHPUnit\Framework\TestCase;

class StageTest extends TestCase
{
    public function test__construct(): Stage
    {
        $stage = new Stage(fn (): bool => true);

        $this->assertInstanceOf(CallableInstance::class, $stage->instance);
        $this->assertSame(Closure::class, $stage->name);
        $this->assertTrue($stage->enabled);
        $this->assertSame(0, $stage->instance->getCount());

        return $stage;
    }

    /**
     * @depends test__construct
     */
    public function test__toString(Stage $stage): Stage
    {
        $this->assertSame(Closure::class, (string) $stage);

        return $stage;
    }

    /**
     * @depends test__toString
     */
    public function test__invoke(Stage $stage): Stage
    {
        $this->assertTrue($stage());
        $this->assertSame(1, $stage->instance->getCount());

        return $stage;
    }

    /**
     * @depends test__invoke
     */
    public function testTransitions(Stage $stage): Stage
    {
        $stage->addTransitions(new Transition('stage'));

        $this->assertCount(1, $stage->getTransitions());

        return $stage;
    }

    /**
     * @depends testTransitions
     */
    public function testSetArtifactNames(Stage $stage): void
    {
        $stage->setArtifactNames('some');

        $this->assertCount(1, $stage->artifactNames);
        $this->assertSame(['some'], $stage->artifactNames);
    }
}
