<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Stage;

use Creatortsv\WorkflowProcess\Utils\CallbackWrapper;
use ReflectionClass;
use ReflectionMethod;

/**
 * @template T
 *
 * @method StageSwitcher switchTo(string $name, int $number = 1)
 * @method StageSwitcher skip(int $length = 0)
 * @method StageSwitcher stop()
 * @method StageSwitcher back()
 * @method StageSwitcher restart()
 * @method StageInfo|null next()
 * @method StageInfo|null previous()
 */
class StageSwitcher
{
    private StageManager $manager;

    /**
     * @var array<string>
     */
    private array $methods;

    public function __construct(StageManager $manager)
    {
        $closure = fn (ReflectionMethod $m): bool => !in_array($m->getShortName(), [
            'getStages',
            'switch',
        ], true);

        $reflect = new ReflectionClass($manager);
        $methods = array_map(fn (ReflectionMethod $m): string => $m->getShortName(), array_filter(
            $reflect->getMethods(ReflectionMethod::IS_PUBLIC),
            $closure,
        ));

        $this->manager = $manager;
        $this->methods = $methods;
    }

    public function __invoke(string $name, int $number = 1): StageSwitcher
    {
        $this->manager->switchTo(
            $name,
            $number,
        );

        return $this;
    }

    /**
     * @param array<T> $arguments
     * @return StageSwitcher|StageInfo|null
     */
    public function __call(string $name, array $arguments)
    {
        $returned = (in_array($name, $this->methods)
            ? $this->manager
            : $this)->$name(...$arguments);

        if ($returned instanceof CallbackWrapper) {
            return StageInfo::of($returned);
        } elseif ($returned instanceof StageManager) {
            return $this;
        }

        return null;
    }

    public function current(): ?StageInfo
    {
        return StageInfo::of($this
            ->manager
            ->getStages()
            ->current());
    }

    /**
     * @deprecated deprecated since version 1.2.0 Use \Creatortsv\WorkflowProcess\Stage\StageSwitcher::sequence()->previous() instead, will be removed since v2.0
     */
    public function prev(): ?StageInfo
    {
        return StageInfo::of($this
            ->manager
            ->previous());
    }
}
