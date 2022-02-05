<?php

namespace Creatortsv\WorkflowProcess\Tests\Stage;

use Creatortsv\WorkflowProcess\Enum\SwitchTo;
use Creatortsv\WorkflowProcess\Exception\StagesNotFoundException;
use Creatortsv\WorkflowProcess\Stage\Stage;
use Creatortsv\WorkflowProcess\Stage\StageManager;
use Exception;
use PHPUnit\Framework\TestCase;

class StageManagerTest extends TestCase
{
    public function test__construct(): StageManager
    {
        $stages = [
            new Stage(fn (): bool => true, true, 'one'),
            new Stage(fn (): bool => true, true, 'two'),
            new Stage(fn (): bool => true, true, 'three'),
            new Stage(fn (): bool => true, true, 'four'),
        ];

        $manager = new StageManager(...$stages);

        $this->assertCount(4, $manager->stages);
        $this->assertSame(0, $manager->stages->key());
        $this->assertSame('one', $manager->stages->current()->name);
        $this->assertNull($manager->previous());
        $this->assertSame('two', $manager->next()->name);
        $this->assertFalse($manager->isBlocked());

        return $manager;
    }

    /**
     * @depends test__construct
     * @throws StagesNotFoundException
     */
    public function testSwitchTo(StageManager $manager): StageManager
    {
        try {
            $manager->switchTo('some');
        } catch (Exception $e) {
            $this->assertInstanceOf(StagesNotFoundException::class, $e);
        }

        $manager->switchTo('three');

        $this->assertTrue($manager->isBlocked());
        $this->assertNull($manager->previous());
        $this->assertSame('three', $manager->next()->name);

        $manager->switch();

        $this->assertFalse($manager->isBlocked());
        $this->assertSame('one', $manager->previous()->name);
        $this->assertSame('three', $manager->stages->current()->name);
        $this->assertSame('four', $manager->next()->name);

        $manager->switchTo(SwitchTo::END)->switch();

        $this->assertSame('three', $manager->previous()->name);
        $this->assertSame(null, $manager->stages->current());
        $this->assertSame(null, $manager->next());

        $manager->switchTo(SwitchTo::BACK)->switch();

        $this->assertSame(null, $manager->previous());
        $this->assertSame('three', $manager->stages->current()->name);
        $this->assertSame('four', $manager->next()->name);

        $manager->switchTo(SwitchTo::REPEAT)->switch();

        $this->assertSame('three', $manager->previous()->name);
        $this->assertSame('three', $manager->stages->current()->name);
        $this->assertSame('four', $manager->next()->name);

        $manager->switchTo(SwitchTo::RETURN)->switch();

        $this->assertSame('three', $manager->previous()->name);
        $this->assertSame('four', $manager->stages->current()->name);
        $this->assertSame(null, $manager->next());

        $manager->switchTo(SwitchTo::START)->switch();

        $this->assertSame('four', $manager->previous()->name);
        $this->assertSame('one', $manager->stages->current()->name);
        $this->assertSame('two', $manager->next()->name);

        return $manager;
    }
}
