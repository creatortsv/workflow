<?php

declare(strict_types=1);

namespace Creatortsv\Workflow\Tests\Proto;

use Creatortsv\Workflow\Exception\StageNotFoundException;
use Creatortsv\Workflow\Processor\Controller;
use Creatortsv\Workflow\Stage\StageInterface;

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