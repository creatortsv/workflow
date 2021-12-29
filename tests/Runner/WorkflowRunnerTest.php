<?php

declare(strict_types=1);

namespace Creatortsv\Workflow\Tests\Runner;

use Creatortsv\Workflow\Runner\WorkflowRunner;
use Creatortsv\Workflow\Tests\Proto\TestDecrementStage;
use Creatortsv\Workflow\Tests\Proto\TestIncrementStage;
use Creatortsv\Workflow\Tests\Proto\TestSubject;
use Creatortsv\Workflow\Workflow;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class WorkflowRunnerTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function test__construct(): WorkflowRunner
    {
        $runner = (new Workflow(
            fn (TestSubject $context): TestSubject => $context->setNum($context->getNum() + 2),
            fn (TestSubject $context): TestSubject => $context->setNum($context->getNum() * 2),
            new TestDecrementStage(),
            new TestIncrementStage(),
        ))->makeRunner(new TestSubject(5));

        $this->assertInstanceOf(WorkflowRunner::class, $runner);

        return $runner;
    }

    /**
     * @depends test__construct
     */
    public function testRun(WorkflowRunner $runner): WorkflowRunner
    {
        $this->assertInstanceOf(WorkflowRunner::class, $runner->run());

        return $runner;
    }

    /**
     * @depends testRun
     * @throws ReflectionException
     */
    public function testThen(WorkflowRunner $runner): void
    {
        $subject = $runner->then(fn (TestSubject $subject): TestSubject => $subject);

        $this->assertInstanceOf(TestSubject::class, $subject);
        $this->assertSame(10, $subject->getNum());
    }
}
