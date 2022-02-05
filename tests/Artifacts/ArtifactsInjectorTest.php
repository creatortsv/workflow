<?php

namespace Creatortsv\WorkflowProcess\Tests\Artifacts;

use Creatortsv\WorkflowProcess\Artifacts\ArtifactsInjector;
use Creatortsv\WorkflowProcess\Artifacts\ArtifactsStorage;
use Creatortsv\WorkflowProcess\Stage\StageInterface;
use Creatortsv\WorkflowProcess\Tests\Proto\Amount;
use Creatortsv\WorkflowProcess\Tests\Proto\CallableProto;
use Creatortsv\WorkflowProcess\Tests\Proto\ExtendedAndImplemented;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class ArtifactsInjectorTest extends TestCase
{
    public function test__construct(): ArtifactsInjector
    {
        $injector = new ArtifactsInjector(new ArtifactsStorage());

        $this->assertInstanceOf(ArtifactsStorage::class, $injector->storage);

        return $injector;
    }

    /**
     * @depends test__construct
     * @throws ReflectionException
     */
    public function testInjectInto(ArtifactsInjector $injector): void
    {
        $injector->storage->set(new Amount());
        $injector->storage->set(new Amount(amount: 3));
        $injector->storage->set('some', 'name');
        $injector->storage->set(new ExtendedAndImplemented());

        $callback = $injector->injectInto(function (
            Amount $amount,
            StageInterface $extendedAsInterface,
            CallableProto $extendedAsParent,
            ExtendedAndImplemented $extended,
            string $name,
            ?string $another = null,
            Amount ...$amounts,
        ): string {
            $this->assertSame(3, $amount->amount);
            $this->assertContainsEquals($amount, $amounts);
            $this->assertSame(1, array_search($amount, $amounts, true));
            $this->assertSame(0, current($amounts)->amount);

            $this->assertEquals($extendedAsInterface, $extendedAsParent);
            $this->assertEquals($extendedAsInterface, $extended);
            $this->assertEquals($extendedAsParent, $extended);

            $this->assertSame('some', $name);
            $this->assertNull($another);

            return 'Done';
        });

        $this->assertSame('Done', $callback());
    }
}
