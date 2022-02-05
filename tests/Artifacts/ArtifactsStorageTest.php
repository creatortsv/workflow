<?php

namespace Creatortsv\WorkflowProcess\Tests\Artifacts;

use Creatortsv\WorkflowProcess\Artifacts\ArtifactsStorage;
use Creatortsv\WorkflowProcess\Stage\StageInterface;
use Creatortsv\WorkflowProcess\Support\Helper\SpyHacker;
use Creatortsv\WorkflowProcess\Tests\Proto\Amount;
use Creatortsv\WorkflowProcess\Tests\Proto\CallableProto;
use Creatortsv\WorkflowProcess\Tests\Proto\ExtendedAndImplemented;
use PHPUnit\Framework\TestCase;

class ArtifactsStorageTest extends TestCase
{
    public function testSet(): ArtifactsStorage
    {
        $storage = new ArtifactsStorage();
        $hacked = SpyHacker::hack($storage);

        $this->assertCount(0, $hacked->artifacts ?? []);
        $this->assertCount(0, $hacked->relations ?? []);

        $storage->set(new Amount());
        $storage->set(new ExtendedAndImplemented());
        $storage->set(new ExtendedAndImplemented());
        $storage->set(2, 'amount');

        $this->assertCount(4, $hacked->artifacts ?? []);
        $this->assertCount(4, $hacked->relations ?? []);

        return $storage;
    }

    /**
     * @depends testSet
     */
    public function testCount(ArtifactsStorage $storage): ArtifactsStorage
    {
        $this->assertSame(4, $storage->count());
        $this->assertSame(1, $storage->count(Amount::class));
        $this->assertSame(1, $storage->count('amount'));
        $this->assertSame(2, $storage->count(StageInterface::class));
        $this->assertSame(2, $storage->count(CallableProto::class));
        $this->assertSame(0, $storage->count('some'));

        return $storage;
    }

    /**
     * @depends testCount
     */
    public function testHas(ArtifactsStorage $storage): ArtifactsStorage
    {
        $this->assertTrue($storage->has(Amount::class));
        $this->assertTrue($storage->has('amount'));
        $this->assertTrue($storage->has(StageInterface::class));
        $this->assertTrue($storage->has(CallableProto::class));
        $this->assertFalse($storage->has('other'));

        return $storage;
    }

    /**
     * @depends testHas
     */
    public function testGet(ArtifactsStorage $storage): ArtifactsStorage
    {
        $this->assertEquals([new Amount()], $storage->get(Amount::class));
        $this->assertInstanceOf(ExtendedAndImplemented::class, current($storage->get(StageInterface::class)));
        $this->assertInstanceOf(ExtendedAndImplemented::class, current($storage->get(CallableProto::class)));
        $this->assertSame(2, current($storage->get('amount')));
        $this->assertSame([], $storage->get('other'));

        return $storage;
    }

    /**
     * @depends testGet
     */
    public function testRemove(ArtifactsStorage $storage): void
    {
        $storage->remove('amount');
        $storage->remove(StageInterface::class);

        $this->assertFalse($storage->has(ExtendedAndImplemented::class));
        $this->assertFalse($storage->has(CallableProto::class));
        $this->assertFalse($storage->has(StageInterface::class));
        $this->assertFalse($storage->has('amount'));
        $this->assertTrue($storage->has(Amount::class));
    }
}
