<?php

declare(strict_types=1);

namespace Thesis\Time;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WallClock::class)]
final class WallClockTest extends TestCase
{
    public function testWithoutTimezone(): void
    {
        $clock = new WallClock();

        $time = $clock->now();

        self::assertLessThanOrEqual(1, time() - $time->getTimestamp());
        self::assertSame(date_default_timezone_get(), $time->getTimezone()->getName());
    }

    public function testWithTimezone(): void
    {
        $timezone = new \DateTimeZone('Arctic/Longyearbyen');
        $clock = new WallClock($timezone);

        $time = $clock->now();

        self::assertLessThanOrEqual(1, time() - $time->getTimestamp());
        self::assertSame($timezone->getName(), $time->getTimezone()->getName());
    }
}
