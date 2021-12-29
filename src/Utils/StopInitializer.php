<?php

declare(strict_types=1);

namespace Creatortsv\Workflow\Utils;

final class StopInitializer
{
    private string $stage;
    private int $times;
    private bool $stopped = false;

    public static function makeOn(string $stage, ?int $onTimes = 1): self
    {
        $onTimes = $onTimes < 1 ? 1 : $onTimes;

        return new self($stage, $onTimes);
    }

    /**
     * @param class-string<callable> $stage
     */
    private function __construct(string $stage, int $times)
    {
        $this->stage = $stage;
        $this->times = $times;
    }

    public function getStage(): string
    {
        return $this->stage;
    }

    public function getOnTimes(): int
    {
        return $this->times;
    }

    public function isStopped(): bool
    {
        return $this->stopped;
    }
}
