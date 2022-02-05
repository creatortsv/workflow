<?php

namespace Creatortsv\WorkflowProcess\Tests\Support;

use Creatortsv\WorkflowProcess\Support\Helper\AttributeReader;
use PHPUnit\Framework\TestCase;
use ReflectionException;

abstract class AbstractAttributeTestCase extends TestCase
{
    /**
     * @throws ReflectionException
     */
    protected function getAttribute(object $object, string $attribute): ?object
    {
        $reader = AttributeReader::of($object);

        [, $attributes] = current($reader->read($attribute));

        return array_pop($attributes);
    }
}
