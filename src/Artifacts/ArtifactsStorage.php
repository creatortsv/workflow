<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Artifacts;

use ArrayAccess;
use Countable;

/**
 * @template T
 */
class ArtifactsStorage implements ArrayAccess, Countable
{
    /**
     * [Value]: the original artifact value (cloned)
     * [Key  ]: the artifact's position
     * @var array<int, T>
     */
    private array $artifacts = [];

    /**
     * [Value]: the artifact's name (class name or function name or callable object name)
     * [Key  ]: the artifact's position in this storage
     * @var array<int, string>
     */
    private array $relations = [];

    /**
     * [Value]: the artifact's type
     * [Key  ]: the artifact's position in this storage
     * @var array<int, string>
     */
    private array $typeLinks = [];

    /**
     * Switches from the relations property to the typeLinks
     */
    private bool $useTypes = false;

    public function has(string $name): bool
    {
        return $this->offsetExists($name);
    }

    /**
     * @return array<T>
     */
    public function get(string $name): array
    {
        return $this->offsetGet($name);
    }

    /**
     * @param T $artifact
     */
    public function set($artifact, ?string $name = null): ArtifactsStorage
    {
        $this->offsetSet($name, $artifact);

        return $this;
    }

    public function remove(string $name): ArtifactsStorage
    {
        $this->offsetUnset($name);

        return $this;
    }

    public function useTypes(bool $switch = true): ArtifactsStorage
    {
        $this->useTypes = $switch;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return in_array($offset, $this->property(), true);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset): array
    {
        $positions = $this->positions($offset);

        return array_filter($this->artifacts, fn (int $position): bool => in_array(
            $position,
            $positions,
            true,
        ), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        $position = $this->count();

        $this->artifacts[$position] = clone $value;
        $this->relations[$position] = $offset;
        $this->typeLinks[$position] = is_object($value)
            ? get_class($value)
            : gettype($value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        $positions = $this->positions($offset);

        foreach ($positions as $position) {
            unset($this->artifacts[$position]);
            unset($this->relations[$position]);
            unset($this->typeLinks[$position]);
        }
    }

    public function count(?string $name = null): int
    {
        if ($name !== null) {
            return count($this->positions($name));
        }

        return count($this->artifacts);
    }

    /**
     * @return array<int>
     */
    protected function positions(string $name): array
    {
        return array_keys($this->property(), $name, true);
    }

    /**
     * @return array<int, T|string>
     */
    protected function property(): array
    {
        return $this->useTypes
            ? $this->typeLinks
            : $this->relations;
    }
}
