<?php

declare(strict_types=1);

namespace Creatortsv\Workflow\Tests\Proto;

class TestSubject
{
    private int $num;

    public function __construct(int $num = 1)
    {
        $this->num = $num;
    }

    public function getNum(): int
    {
        return $this->num;
    }

    public function setNum(int $num): TestSubject
    {
        $this->num = $num;

        return $this;
    }
}
