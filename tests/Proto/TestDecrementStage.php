<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Tests\Proto;

use Creatortsv\WorkflowProcess\Stage\StageInterface;

class TestDecrementStage implements StageInterface
{
    public function __invoke(TestSubject $subject): TestSubject
    {
        $subject->setNum($subject->getNum() - 3);

        return $subject;
    }
}