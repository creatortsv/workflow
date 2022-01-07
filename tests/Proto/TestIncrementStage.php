<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Tests\Proto;

use Closure;
use Creatortsv\WorkflowProcess\Stage\StageInterface;
use Creatortsv\WorkflowProcess\Stage\StageSwitcher;

class TestIncrementStage implements StageInterface
{
    public function __invoke(TestSubject $subject, StageSwitcher $next): TestSubject
    {
        $subject->setNum($subject->getNum() + 1);

        if ($subject->getNum() === 6) {
            $next(TestDecrementStage::class);
        } elseif ($subject->getNum() === 4) {
            $next->switchTo(Closure::class, 1);
        }

        return $subject;
    }

    public function some(): bool
    {
        return true;
    }
}