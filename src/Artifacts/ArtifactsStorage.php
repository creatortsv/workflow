<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Artifacts;

use Countable;

class ArtifactsStorage implements Countable
{
    /**
     * [Value]: the original artifact value
     * [Key  ]: the artifact's position
     * @var array<int, mixed>
     */
    private array $artifacts = [];

    /**
     * [Value]: the artifact's type or name
     * [Key  ]: the artifact's position in this storage
     * @var array<int, string>
     */
    private array $relations = [];

    public function has(string $nameOrType): bool
    {
        return $this->count($nameOrType) > 0;
    }

    /**
     * @return array<int, mixed>
     */
    public function get(string $nameOrType): array
    {
        $positions = $this->positions($nameOrType);

        return array_filter($this->artifacts, fn (int $position): bool => in_array(
            $position,
            $positions,
            true,
        ), ARRAY_FILTER_USE_KEY);
    }

    public function set(mixed $artifact, ?string $name = null): ArtifactsStorage
    {
        $position = $this->count();

        $this->artifacts[$position] = $artifact;
        $this->relations[$position] = $name ?? get_debug_type($artifact);

        return $this;
    }

    public function remove(string $nameOrType): ArtifactsStorage
    {
        foreach (array_reverse($this->positions($nameOrType)) as $position) {
            array_splice($this->artifacts, $position, 1);
            array_splice($this->relations, $position, 1);
        }

        return $this;
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
        $filter = function (string $nameOrType) use ($name): bool {
            $isMatched = $name === $nameOrType;

            if ($isMatched !== true) {
                $isMatched = class_exists($nameOrType) && is_subclass_of($nameOrType, $name);
            }

            if ($isMatched !== true) {
                $isMatched = interface_exists($nameOrType)
                    && in_array($name, class_implements($nameOrType), true);
            }

            return $isMatched;
        };

        return array_keys(array_filter($this->relations, $filter));
    }
}
