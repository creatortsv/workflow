<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Tests\Artifacts;

use Creatortsv\WorkflowProcess\Artifacts\ArtifactsStorage;
use Creatortsv\WorkflowProcess\Tests\Proto\TestSubject;
use Iterator;
use PHPUnit\Framework\TestCase;

class ArtifactsStorageTest extends TestCase
{
    public function dataProvider(): Iterator
    {
        yield 'With names of artifacts' => [false, 'some'];
        yield 'With types of artifacts' => [true, TestSubject::class];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHas(bool $useTypes, string $value): void
    {
        $this->assertTrue($this
            ->makeStorage()
            ->useTypes($useTypes)
            ->has($value));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGet(bool $useTypes, string $value): void
    {
        $artifacts = $this
            ->makeStorage()
            ->useTypes($useTypes)
            ->get($value);

        $this->assertCount(1, $artifacts);
        $this->assertInstanceOf(TestSubject::class, current($artifacts));
    }

    public function testSet(): ArtifactsStorage
    {
        $storage = $this->makeStorage();

        $this->assertInstanceOf(ArtifactsStorage::class, $storage
            ->set(new TestSubject(2), 'some')
            ->set(new TestSubject(5)));

        $this->assertCount(3, $storage);
        $this->assertCount(3, $artifacts = $storage
            ->useTypes(true)
            ->get(TestSubject::class));

        $this->assertCount(2, $storage->get('some'));

        foreach ($artifacts as $artifact) {
            $this->assertInstanceOf(TestSubject::class, $artifact);
        }

        $this->assertCount(3, array_unique($artifacts, SORT_REGULAR));

        return $storage;
    }

    /**
     * @depends testSet
     */
    public function testRemove(ArtifactsStorage $storage): void
    {
        $this->assertInstanceOf(ArtifactsStorage::class, $storage->remove('some'));
        $this->assertCount(1, $storage);
        $this->assertCount(0, $storage
            ->useTypes(true)
            ->remove(TestSubject::class));
    }

    private function makeStorage(): ArtifactsStorage
    {
        $storage = new ArtifactsStorage();
        $storage->set(new TestSubject(), 'some');

        return $storage;
    }
}
