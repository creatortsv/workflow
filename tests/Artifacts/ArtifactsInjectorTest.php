<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Tests\Artifacts;

use Closure;
use Creatortsv\WorkflowProcess\Artifacts\ArtifactsInjector;
use Creatortsv\WorkflowProcess\Artifacts\ArtifactsStorage;
use Creatortsv\WorkflowProcess\Tests\Proto\TestSubject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class ArtifactsInjectorTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testInjectInto(): ArtifactsInjector
    {
        $injector = new ArtifactsInjector(
            (new ArtifactsStorage())
                ->set(new TestSubject(1))
                ->set(new TestSubject(2))
                ->set(new TestSubject(3)),
        );

        $callback = function (
            TestSubject $subject,
            ?ArtifactsStorage $storage = null,
            TestSubject ...$subjects
        ): void {
            $this->assertNull($storage);
            $this->assertSame(3, $subject->getNum());
            $this->assertCount(3, $subjects);

            foreach ($subjects as $index => $subject) {
                $this->assertSame($index + 1, $subject->getNum());
            }
        };

        $callback = $injector->injectInto($callback);

        $this->assertInstanceOf(Closure::class, $callback);
        $callback();

        return $injector;
    }

    /**
     * @depends testInjectInto
     */
    public function testGetStorage(ArtifactsInjector $injector): void
    {
        $this->assertInstanceOf(ArtifactsStorage::class, $injector->getStorage());
    }
}
