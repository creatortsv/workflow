<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Tests\Stage;

use ArrayIterator;
use Closure;
use Creatortsv\WorkflowProcess\Stage\StageInfo;
use Creatortsv\WorkflowProcess\Stage\StageSwitcher;
use Creatortsv\WorkflowProcess\Tests\Proto\TestDecrementStage;
use Creatortsv\WorkflowProcess\Tests\Proto\TestSubject;
use Creatortsv\WorkflowProcess\Utils\CallbackWrapper;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class StageSwitcherTest extends TestCase
{
    private static ?ArrayIterator $stages = null;

    /**
     * @throws ReflectionException
     */
    public function test__construct(): StageSwitcher
    {
        $closures = $this::makeStages(true);
        $switcher = new StageSwitcher($closures);

        $current = $this->getExecutedStageInfo($closures->current());

        $this->assertNull($switcher->prev());
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
        $switcher->switch();
        $closures = $this::makeStages();
        $current = $this->getExecutedStageInfo($closures->current(), new TestSubject());

        $this->assertStageInfo($switcher->prev(), Closure::class, 1, 1);
        $this->assertStageInfo($current, TestDecrementStage::class, 1, 1);
        $this->assertStageInfo($switcher->next(), TestDecrementStage::class, 0, 2);

        $switcher->switch();
        $current = $this->getExecutedStageInfo($closures->current(), new TestSubject());

        $this->assertStageInfo($switcher->prev(), TestDecrementStage::class, 1, 1);
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
        $switcher(TestDecrementStage::class, 1);
        $closures = $this::makeStages();
        $current = $this->getExecutedStageInfo($closures->current(), new TestSubject());

        $this->assertStageInfo($switcher->prev(), TestDecrementStage::class, 1, 1);
        $this->assertStageInfo($current, TestDecrementStage::class, 2, 2);
        $this->assertStageInfo($switcher->next(), TestDecrementStage::class, 1, 1);

        $switcher->switch();
        $current = $this->getExecutedStageInfo($closures->current(), new TestSubject());

        $this->assertStageInfo($switcher->prev(), TestDecrementStage::class, 2, 2);
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
    private static function makeStages(bool $fresh = false): ArrayIterator
    {
        if (static::$stages !== null) {
            $fresh && static::$stages->rewind();
        } else {
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

            static::$stages = $stages;
        }

        return static::$stages;
    }

    private function getExecutedStageInfo(CallbackWrapper $stage, object ...$arguments): StageInfo
    {
        $stage(...$arguments);

        return new StageInfo($stage);
    }
}
