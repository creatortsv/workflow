<?php

declare(strict_types=1);

namespace Creatortsv\Workflow\Tests\Proto;

use Creatortsv\Workflow\Stage\StageInterface;

class TestDecrementStage implements StageInterface
{
    public function __invoke(TestSubject $subject): TestSubject
    {
        $subject->setNum($subject->getNum() - 3);

        return $subject;
    }
}