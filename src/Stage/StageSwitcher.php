<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Stage;

use Creatortsv\WorkflowProcess\Exception\StagesNotFoundException;
use ReflectionClass;
use ReflectionMethod;

/**
 * @template T
 *
 * @method StageSwitcher switchTo(string $name)
 * @method Stage|null previous()
 * @method Stage|null next()
 */
class StageSwitcher
{
    /**
     * @var string[]
     */
    private array $methods;

    public function __construct(private StageManager $manager)
    {
        $closure = fn (ReflectionMethod $m): bool => !in_array($m->getShortName(), [
            'isBlocked',
            'switch',
        ], true);

        $reflect = new ReflectionClass($manager);
        $methods = array_map(fn (ReflectionMethod $m): string => $m->getShortName(), array_filter(
            $reflect->getMethods(ReflectionMethod::IS_PUBLIC),
            $closure,
        ));

        $this->methods = $methods;
    }

    /**
     * @throws StagesNotFoundException
     */
    public function __invoke(string $name): StageSwitcher
    {
        $this->manager->switchTo($name);

        return $this;
    }

    public function __call(string $name, array $arguments): ?StageSwitcher
    {
        $returned = (in_array($name, $this->methods)
            ? $this->manager
            : $this)->$name(...$arguments);

        if ($returned instanceof StageManager) {
            return $this;
        }

        return null;
    }
}
