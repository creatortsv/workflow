<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Tests\Proto;

use Creatortsv\WorkflowProcess\Exception\StageNotFoundException;
use Creatortsv\WorkflowProcess\Processor\Controller;
use Creatortsv\WorkflowProcess\Stage\StageInterface;

class TestIncrementStage implements StageInterface
{
    /**
     * @throws StageNotFoundException
     */
    public function __invoke(TestSubject $subject, Controller $next): TestSubject
    {
        $subject->setNum($subject->getNum() + 1);

        if ($subject->getNum() === 12) {
            $next(TestDecrementStage::class);
        }

        return $subject;
    }
}