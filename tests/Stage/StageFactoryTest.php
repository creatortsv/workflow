<?php

namespace Creatortsv\WorkflowProcess\Tests\Stage;

use Creatortsv\WorkflowProcess\Stage\Stage;
use Creatortsv\WorkflowProcess\Stage\StageFactory;
use Creatortsv\WorkflowProcess\Support;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class StageFactoryTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testCreate(): void
    {
        $callable = new #[
            Support\Stage('stage.init'),
            Support\Transition('stage.1'),
            Support\Transition('stage.2'),
        ] class {
            #[Support\Transition('stage.3', 'stage.1')]
            #[Support\Transition('stage.4', 'stage.2')]
            public bool $done = false;

            #[Support\Transition('stage.5')]
            public function toSecond(): bool
            {
                return $this->done;
            }

            public function __invoke(): void {}
        };

        $stage = StageFactory::create($callable);
        $items = [
            ...$stage->getTransitions('stage.1'),
            ...$stage->getTransitions('stage.2'),
            ...$stage->getTransitions(),
        ];

        $this->assertInstanceOf(Stage::class, $stage);
        $this->assertSame('stage.init', $stage->name);
        $this->assertCount(11, $items);
    }
}
