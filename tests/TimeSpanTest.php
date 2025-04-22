<?php

declare(strict_types=1);

namespace Thesis\Time;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeSpan::class)]
final class TimeSpanTest extends TestCase
{
    #[TestWith(['@123.003', '@124.005', -1002])]
    #[TestWith(['@124.005', '@123.003', 1002])]
    public function testDiff(string $a, string $b, int $expectedDiffUs): void
    {
        $diff = TimeSpan::diff(new \DateTimeImmutable($a), new \DateTimeImmutable($b));

        self::assertSame($expectedDiffUs, $diff->toMicroseconds());
    }

    /**
     * @param array{days?: float|int, hours?: float|int, minutes?: float|int, seconds?: float|int, milliseconds?: float|int, microseconds?: float|int} $args
     */
    #[TestWith([
        ['seconds' => 987, 'milliseconds' => 654, 'microseconds' => 321],
        987654321,
    ])]
    #[TestWith([
        ['milliseconds' => -1.555, 'microseconds' => -445],
        -2000,
    ])]
    public function testFrom(array $args, int $expected): void
    {
        $timeSpan = TimeSpan::from(...$args);

        self::assertSame($expected, $timeSpan->toMicroseconds());
    }

    #[TestWith([100, 100])]
    #[TestWith([100.1, 100])]
    #[TestWith([100.5, 101])]
    #[TestWith([100.99999, 101])]
    public function testFromMicroseconds(int|float $microseconds, int $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expected, $span->toMicroseconds());
    }

    #[TestWith([100, 100000])]
    #[TestWith([100.1, 100100])]
    #[TestWith([100.5, 100500])]
    #[TestWith([100.99999, 101000])]
    public function testFromMilliseconds(int|float $milliseconds, int $expected): void
    {
        $span = TimeSpan::fromMilliseconds($milliseconds);

        self::assertSame($expected, $span->toMicroseconds());
    }
}
