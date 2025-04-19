<?php

declare(strict_types=1);

namespace Thesis\Time;

use Psr\Clock\ClockInterface;

/**
 * @api
 */
final readonly class WallClock implements ClockInterface
{
    public function __construct(
        private ?\DateTimeZone $timeZone = null,
    ) {}

    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable(timezone: $this->timeZone);
    }
}
