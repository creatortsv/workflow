<?php

declare(strict_types=1);

namespace Creatortsv\WorkflowProcess\Processor;

use ArrayIterator;
use Closure;
use Creatortsv\WorkflowProcess\Artifacts\ArtifactsInjector;
use Creatortsv\WorkflowProcess\Artifacts\ArtifactsStorage;
use League\Pipeline\ProcessorInterface;

/**
 * @template T
 */
final class Processor implements ProcessorInterface
{
    private ArtifactsInjector $injector;

    public function __construct(ArtifactsInjector $injector)
    {
        $this->injector = $injector;
    }

    public function getInjector(): ArtifactsInjector
    {
        return $this->injector;
    }

    /**
     * @inheritdoc
     */
    public function process($payload, callable ...$stages): void
    {
        $this->getInjector()
            ->getStorage()
            ->set(new Controller(...$stages));

        $this->handler()($payload);
    }

    private function handler(): Closure
    {
        return function (ArrayIterator $payload): void {
            $payload = $payload->getArrayCopy();
            $storage = $this
                ->getInjector()
                ->getStorage();

            $controller = $storage
                ->useTypes()
                ->get(Controller::class);

            $controller = current($controller);

            array_walk($payload, fn ($context): ArtifactsStorage => $storage->set($context));

            while ($stage = $controller->stage()) {
                $name = (string) $stage;
                $data = $this
                    ->getInjector()
                    ->injectInto($stage)();

                if (!$data instanceof Controller) {
                    $storage->set($data, $name);
                }

                if ($name === (string) $controller->stage()) {
                    $controller->next();
                }
            }
        };
    }
}
