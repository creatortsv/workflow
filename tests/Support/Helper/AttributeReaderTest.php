<?php

namespace Creatortsv\WorkflowProcess\Tests\Support\Helper;

use Creatortsv\WorkflowProcess\Support\Helper\AttributeReader;
use Creatortsv\WorkflowProcess\Tests\Proto\Attribute\ProtoAttribute;
use Iterator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

class AttributeReaderTest extends TestCase
{
    public function ofProvider(): Iterator
    {
        $object = new class {
            public function __invoke(): void {}
            public function method(): void {}
            public static function staticMethod(): void {}
        };

        yield 'Just object' => [new class {}];
        yield 'Anonymous function' => [fn () => true];
        yield 'Function from method' => [$object->method(...)];
        yield 'Callable object' => [$object];
        yield 'Callable array' => [[$object, 'method']];
        yield 'Callable array static' => [[$object::class, 'staticMethod']];
    }

    /**
     * @dataProvider ofProvider
     */
    public function testOf(callable|object $instance): void
    {
        $reader = AttributeReader::of($instance);

        $this->assertInstanceOf(AttributeReader::class, $reader);
        $this->assertEquals(is_object($instance)
            ? $instance
            : $instance(...), $reader->object);
    }

    public function readProvider(): Iterator
    {
        yield 'Root only' => [AttributeReader::INCLUDE_ROOT];
        yield 'Root & methods' => [AttributeReader::INCLUDE_ROOT | AttributeReader::INCLUDE_METHODS];
        yield 'Root & properties' => [AttributeReader::INCLUDE_ROOT | AttributeReader::INCLUDE_PROPERTIES];
        yield 'Methods only' => [AttributeReader::INCLUDE_METHODS];
        yield 'Methods & properties' => [AttributeReader::INCLUDE_METHODS | AttributeReader::INCLUDE_PROPERTIES];
        yield 'Properties only' => [AttributeReader::INCLUDE_PROPERTIES];
        yield 'Root & methods & properties' => [AttributeReader::INCLUDE_ROOT
            | AttributeReader::INCLUDE_METHODS
            | AttributeReader::INCLUDE_PROPERTIES];
    }

    /**
     * @dataProvider readProvider
     * @throws ReflectionException
     */
    public function testRead(int $flags): void
    {
        $object = new #[ProtoAttribute] class {
            #[ProtoAttribute]
            #[ProtoAttribute]
            public int $visible;

            #[ProtoAttribute] // Should be ignored
            private int $hidden;

            #[ProtoAttribute]
            #[ProtoAttribute]
            public function visibleMethod(): void {}

            #[ProtoAttribute] // Should be ignored
            private function hiddenMethod(): void {}
        };

        $reader = AttributeReader::of($object);
        $attribs = $reader->read(ProtoAttribute::class, $flags);

        $this->assertCount(match ($flags) {
            AttributeReader::INCLUDE_ROOT,
            AttributeReader::INCLUDE_METHODS,
            AttributeReader::INCLUDE_PROPERTIES => 1,

            AttributeReader::INCLUDE_ROOT | AttributeReader::INCLUDE_METHODS,
            AttributeReader::INCLUDE_ROOT | AttributeReader::INCLUDE_PROPERTIES,
            AttributeReader::INCLUDE_METHODS | AttributeReader::INCLUDE_PROPERTIES => 2,

            AttributeReader::INCLUDE_ROOT
                | AttributeReader::INCLUDE_METHODS
                | AttributeReader::INCLUDE_PROPERTIES => 3,
        }, $attribs);

        foreach ($attribs as [$reflect, $attributes]) {
            $this->assertNotEmpty($attributes);
            $this->assertInstanceOf(Reflector::class, $reflect);
            $this->assertCount($count = match ($reflect::class) {
                ReflectionClass::class => 1,
                ReflectionMethod::class, ReflectionProperty::class => 2,
            }, $attributes);

            foreach ($attributes as $attribute) {
                $this->assertInstanceOf(ProtoAttribute::class, $attribute);
            }
        }
    }
}
