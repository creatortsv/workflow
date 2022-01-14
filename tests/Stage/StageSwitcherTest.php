<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Tests\Stage;

use ArrayIterator;
use Closure;
use Creatortsv\WorkflowProcess\Stage\StageInfo;
use Creatortsv\WorkflowProcess\Stage\StageManager;
use Creatortsv\WorkflowProcess\Stage\StageSwitcher;
use Creatortsv\WorkflowProcess\Tests\Proto\TestDecrementStage;
use Creatortsv\WorkflowProcess\Tests\Proto\TestSubject;
use Creatortsv\WorkflowProcess\Utils\CallbackWrapper;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;

class StageSwitcherTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function test__construct(): StageSwitcher
    {
        $manager = new StageManager(... (array) $this::makeStages());
        $switcher = new StageSwitcher($manager);

        $current = $this->getExecutedStageInfo($manager->getStages()->current());

        $this->assertNull($switcher->previous());
        $this->assertStageInfo($current, Closure::class, 1, 1);
        $this->assertStageInfo($switcher->next(), TestDecrementStage::class, 0, 1);

        return $switcher;
    }

    /**
     * @depends test__construct
     * @throws ReflectionException
     */
    public function testSwitch(StageSwitcher $switcher): StageSwitcher
    {
        $manager = $this->getManager($switcher);
        $manager->switch();

        $current = $this->getExecutedStageInfo($manager->getStages()->current(), new TestSubject());

        $this->assertStageInfo($switcher->previous(), Closure::class, 1, 1);
        $this->assertStageInfo($current, TestDecrementStage::class, 1, 1);
        $this->assertStageInfo($switcher->next(), TestDecrementStage::class, 0, 2);

        $manager->switch();
        $current = $this->getExecutedStageInfo($manager->getStages()->current(), new TestSubject());

        $this->assertStageInfo($switcher->previous(), TestDecrementStage::class, 1, 1);
        $this->assertStageInfo($current, TestDecrementStage::class, 1, 2);
        $this->assertNull($switcher->next());

        return $switcher;
    }

    /**
     * @depends testSwitch
     * @throws ReflectionException
     */
    public function testSwitchTo(StageSwitcher $switcher): void
    {
        $switcher(TestDecrementStage::class);
        $manager = $this->getManager($switcher);
        $current = $this->getExecutedStageInfo($manager->getStages()->current(), new TestSubject());

        $this->assertStageInfo($switcher->previous(), TestDecrementStage::class, 1, 1);
        $this->assertStageInfo($current, TestDecrementStage::class, 2, 2);
        $this->assertStageInfo($switcher->next(), TestDecrementStage::class, 1, 1);

        $manager->switch();
        $current = $this->getExecutedStageInfo($manager->getStages()->current(), new TestSubject());

        $this->assertStageInfo($switcher->previous(), TestDecrementStage::class, 2, 2);
        $this->assertStageInfo($current, TestDecrementStage::class, 2, 1);
        $this->assertStageInfo($switcher->next(), TestDecrementStage::class, 2, 2);
    }

    private function assertStageInfo(
        StageInfo $stage,
        string $name,
        int $executed,
        int $number
    ): void {
        $this->assertInstanceOf(StageInfo::class, $stage);
        $this->assertSame($name, $stage->name());
        $this->assertSame($executed, $stage->getExecutedTimes());
        $this->assertSame($number, $stage->number());
    }

    /**
     * @throws ReflectionException
     */
    private static function makeStages(): ArrayIterator
    {
        $number = [];
        $stages = new ArrayIterator();
        $inputs = [
            fn () => true,
            new TestDecrementStage(),
            new TestDecrementStage(),
        ];

        foreach ($inputs as $stage) {
            $name = CallbackWrapper::of($stage)->name();
            $number[$name] ??= 0;
            $number[$name] ++ ;
            $stages->append(CallbackWrapper::of($stage, $number[$name]));
        }

        return $stages;
    }

    private function getExecutedStageInfo(CallbackWrapper $stage, object ...$arguments): StageInfo
    {
        $stage(...$arguments);

        return StageInfo::of($stage);
    }

    /**
     * @throws ReflectionException
     */
    private function getManager(StageSwitcher $switcher): StageManager
    {
        $property = new ReflectionProperty($switcher, 'manager');
        $property->setAccessible(true);

        return $property->getValue($switcher);
    }
}
