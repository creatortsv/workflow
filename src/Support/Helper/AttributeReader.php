<?php

namespace Creatortsv\WorkflowProcess\Support\Helper;

use Closure;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionProperty;

class AttributeReader
{
    public const INCLUDE_ROOT = 1;
    public const INCLUDE_METHODS = 2;
    public const INCLUDE_PROPERTIES = 4;

    /**
     * @throws ReflectionException
     */
    public function read(string $attribute, int $flags = self::INCLUDE_ROOT): array
    {
        if (!$flags) {
            return [];
        }

        $reflects = [];
        $root = $this->object instanceof Closure
            ? new ReflectionFunction($this->object)
            : new ReflectionClass($this->object);

        if ($flags % 2 === self::INCLUDE_ROOT) {
        /** flags variable contains INCLUDE_ROOT (one of - 1, 3, 5, 7) */
            $flags -- ;
            $reflects[] = $root;
        }

        if ($root instanceof ReflectionClass) {
            while ($flags > 0) {
                array_push($reflects, ...match ($flags) {
                    self::INCLUDE_METHODS => array_filter($root->getMethods(
                        ReflectionMethod::IS_PUBLIC,
                    ), fn (ReflectionMethod $m): bool => !$m->isAbstract() && !$m->isConstructor()),

                    self::INCLUDE_PROPERTIES,
                    self::INCLUDE_PROPERTIES | self::INCLUDE_METHODS => $root->getProperties(
                        ReflectionProperty::IS_PUBLIC,
                    ),
                });

                $flags -= self::INCLUDE_PROPERTIES;
            }
        }

        $initiate = fn (ReflectionAttribute $a) => $a->newInstance();
        $doFilter = fn (array $attribs): bool => count(($attribs[1] ?? []));
        $mappings = fn (ReflectionClass|ReflectionFunctionAbstract|ReflectionProperty $reflect): array => [
            $reflect, array_map($initiate, $reflect->getAttributes($attribute)),
        ];

        return array_filter(array_map(
            $mappings,
            $reflects,
        ), $doFilter);
    }

    public static function of(callable|object $object): AttributeReader
    {
        return new AttributeReader(is_object($object)
            ? $object
            : $object(...));
    }

    private function __construct(public readonly object $object)
    {

    }
}
