<?php

declare(strict_types=1);

namespace Thesis\Time;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeSpan::class)]
final class TimeSpanTest extends TestCase
{
    #[TestWith(['@123.00333', '@124.00555', -1_002_220])]
    #[TestWith(['@124.00555', '@123.00333', 1_002_220])]
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
        987_654_321,
    ])]
    #[TestWith([
        ['milliseconds' => -1.555, 'microseconds' => -445],
        -2000,
    ])]
    #[TestWith([
        ['days' => 7, 'hours' => 12, 'minutes' => 49, 'seconds' => 35, 'milliseconds' => 222, 'microseconds' => 333],
        650_975_222_333,
    ])]
    #[TestWith([
        ['days' => 7, 'hours' => 12, 'minutes' => 49, 'seconds' => 35, 'milliseconds' => 222],
        650_975_222_000,
    ])]
    #[TestWith([
        ['days' => 7, 'hours' => 12, 'minutes' => 49, 'seconds' => 35, 'milliseconds' => 222, 'nanoseconds' => 10000],
        650_975_222_010,
    ])]
    #[TestWith([
        ['days' => 7, 'hours' => 12, 'minutes' => 49, 'seconds' => 35],
        650_975_000_000,
    ])]
    #[TestWith([
        ['days' => 7, 'hours' => 12, 'minutes' => 49],
        650_940_000_000,
    ])]
    #[TestWith([
        ['days' => 7, 'hours' => 12],
        648_000_000_000,
    ])]
    #[TestWith([
        ['days' => 7],
        604_800_000_000,
    ])]
    public function testFrom(array $args, int $expected): void
    {
        $timeSpan = TimeSpan::from(...$args);

        self::assertSame($expected, $timeSpan->toMicroseconds());
    }

    #[TestWith([100, 100])]
    #[TestWith([100.1, 100])]
    #[TestWith([100.5, 101])]
    #[TestWith([100.99_999, 101])]
    public function testFromNanoseconds(int|float $nanoseconds, int $expected): void
    {
        $span = TimeSpan::fromNanoseconds($nanoseconds);

        self::assertSame($expected, $span->toNanoseconds());
    }

    #[TestWith([100, 100])]
    #[TestWith([100.1, 100])]
    #[TestWith([100.5, 101])]
    #[TestWith([100.99_999, 101])]
    public function testFromMicroseconds(int|float $microseconds, int $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expected, $span->toMicroseconds());
    }

    #[TestWith([100, 100_000])]
    #[TestWith([100.1, 100_100])]
    #[TestWith([100.5, 100_500])]
    #[TestWith([100.99_999, 101_000])]
    public function testFromMilliseconds(int|float $milliseconds, int $expected): void
    {
        $span = TimeSpan::fromMilliseconds($milliseconds);

        self::assertSame($expected, $span->toMicroseconds());
    }

    #[TestWith([100, 100_000_000])]
    #[TestWith([100.1, 100_100_000])]
    #[TestWith([100.5, 100_500_000])]
    #[TestWith([100.99_999, 100_999_990])]
    public function testFromSeconds(int|float $seconds, int $expected): void
    {
        $span = TimeSpan::fromSeconds($seconds);

        self::assertSame($expected, $span->toMicroseconds());
    }

    #[TestWith([100, 6_000_000_000])]
    #[TestWith([100.1, 6_006_000_000])]
    #[TestWith([100.5, 6_030_000_000])]
    #[TestWith([100.99_999, 6_059_999_400])]
    public function testFromMinutes(int|float $minutes, int $expected): void
    {
        $span = TimeSpan::fromMinutes($minutes);

        self::assertSame($expected, $span->toMicroseconds());
    }

    #[TestWith([100, 360_000_000_000])]
    #[TestWith([100.1, 360_360_000_000])]
    #[TestWith([100.5, 361_800_000_000])]
    #[TestWith([100.99_999, 363_599_964_000])]
    public function testFromHours(int|float $hours, int $expected): void
    {
        $span = TimeSpan::fromHours($hours);

        self::assertSame($expected, $span->toMicroseconds());
    }

    #[TestWith([100, 8_640_000_000_000])]
    #[TestWith([100.1, 8_648_640_000_000])]
    #[TestWith([100.5, 8_683_200_000_000])]
    #[TestWith([100.99_999, 8_726_399_136_000])]
    public function testFromDays(int|float $days, int $expected): void
    {
        $span = TimeSpan::fromDays($days);

        self::assertSame($expected, $span->toMicroseconds());
    }

    #[TestWith([100_000_000, 100.0, 100])]
    #[TestWith([100_100_000, 100.1, 100])]
    #[TestWith([100_500_000, 100.5, 101])]
    #[TestWith([100_999_000, 101.0, 101])]
    public function testToMicroseconds(int $nanoseconds, float $expectedWithPositivePrecision, int $expectedWithLessOrEqualZeroPrecision): void
    {
        $span = TimeSpan::fromNanoseconds($nanoseconds);

        self::assertSame($expectedWithPositivePrecision, $span->toMilliseconds(1));
        self::assertSame($expectedWithLessOrEqualZeroPrecision, $span->toMilliseconds());
    }

    #[TestWith([100_000, 100.0, 100])]
    #[TestWith([100_100, 100.1, 100])]
    #[TestWith([100_500, 100.5, 101])]
    #[TestWith([100_999, 101.0, 101])]
    public function testToMilliseconds(int $microseconds, float $expectedWithPositivePrecision, int $expectedWithLessOrEqualZeroPrecision): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expectedWithPositivePrecision, $span->toMilliseconds(1));
        self::assertSame($expectedWithLessOrEqualZeroPrecision, $span->toMilliseconds());
    }

    #[TestWith([100_000_000, 100.0, 100])]
    #[TestWith([100_100_000, 100.1, 100])]
    #[TestWith([100_500_000, 100.5, 101])]
    #[TestWith([100_999_990, 101.0, 101])]
    public function testToSeconds(int $microseconds, float $expectedWithPositivePrecision, int $expectedWithLessOrEqualZeroPrecision): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expectedWithPositivePrecision, $span->toSeconds(1));
        self::assertSame($expectedWithLessOrEqualZeroPrecision, $span->toSeconds());
    }

    #[TestWith([6_000_000_000, 100.0, 100])]
    #[TestWith([6_006_000_000, 100.1, 100])]
    #[TestWith([6_030_000_000, 100.5, 101])]
    #[TestWith([6_059_999_400, 101.0, 101])]
    public function testToMinutes(int $microseconds, float $expectedWithPositivePrecision, int $expectedWithLessOrEqualZeroPrecision): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expectedWithPositivePrecision, $span->toMinutes(1));
        self::assertSame($expectedWithLessOrEqualZeroPrecision, $span->toMinutes());
    }

    #[TestWith([360_000_000_000, 100.0, 100])]
    #[TestWith([360_360_000_000, 100.1, 100])]
    #[TestWith([361_800_000_000, 100.5, 101])]
    #[TestWith([363_599_964_000, 101.0, 101])]
    public function testToHours(int $microseconds, float $expectedWithPositivePrecision, int $expectedWithLessOrEqualZeroPrecision): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expectedWithPositivePrecision, $span->toHours(1));
        self::assertSame($expectedWithLessOrEqualZeroPrecision, $span->toHours());
    }

    #[TestWith([8_640_000_000_000, 100.0, 100])]
    #[TestWith([8_648_640_000_000, 100.1, 100])]
    #[TestWith([8_683_200_000_000, 100.5, 101])]
    #[TestWith([8_726_399_136_000, 101.0, 101])]
    public function testToDays(int $microseconds, float $expectedWithPositivePrecision, int $expectedWithLessOrEqualZeroPrecision): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expectedWithPositivePrecision, $span->toDays(1));
        self::assertSame($expectedWithLessOrEqualZeroPrecision, $span->toDays());
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000])]
    #[TestWith([-8_640_000_000_000, 8_640_000_000_000])]
    #[TestWith([0, 0])]
    public function testAbs(int $microseconds, int $expectedMicroseconds): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expectedMicroseconds, $span->abs()->toMicroseconds());
    }

    #[TestWith([8_640_000_000_000, -8_640_000_000_000])]
    #[TestWith([-8_640_000_000_000, 8_640_000_000_000])]
    public function testNegated(int $microseconds, int $expectedMicroseconds): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expectedMicroseconds, $span->negated()->toMicroseconds());
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000, 0])]
    #[TestWith([8_640_000_000_000, 360_000_000_000, 1])]
    #[TestWith([360_000_000_000, 8_640_000_000_000, -1])]
    public function testCompareTo(int $firstMicroseconds, int $secondMicroseconds, int $expectedCompare): void
    {
        $first = TimeSpan::fromMicroseconds($firstMicroseconds);
        $second = TimeSpan::fromMicroseconds($secondMicroseconds);

        self::assertSame($expectedCompare, $first->compareTo($second));
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000, true])]
    #[TestWith([8_640_000_000_000, 360_000_000_000, false])]
    #[TestWith([360_000_000_000, 8_640_000_000_000, false])]
    public function testIsEqualTo(int $firstMicroseconds, int $secondMicroseconds, bool $expected): void
    {
        $first = TimeSpan::fromMicroseconds($firstMicroseconds);
        $second = TimeSpan::fromMicroseconds($secondMicroseconds);

        self::assertSame($expected, $first->isEqualTo($second));
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000, false])]
    #[TestWith([8_640_000_000_000, 360_000_000_000, false])]
    #[TestWith([360_000_000_000, 8_640_000_000_000, true])]
    public function testLessThan(int $firstMicroseconds, int $secondMicroseconds, bool $expected): void
    {
        $first = TimeSpan::fromMicroseconds($firstMicroseconds);
        $second = TimeSpan::fromMicroseconds($secondMicroseconds);

        self::assertSame($expected, $first->isLessThan($second));
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000, true])]
    #[TestWith([8_640_000_000_000, 360_000_000_000, false])]
    #[TestWith([360_000_000_000, 8_640_000_000_000, true])]
    public function testIsLessThanOrEqualTo(int $firstMicroseconds, int $secondMicroseconds, bool $expected): void
    {
        $first = TimeSpan::fromMicroseconds($firstMicroseconds);
        $second = TimeSpan::fromMicroseconds($secondMicroseconds);

        self::assertSame($expected, $first->isLessThanOrEqualTo($second));
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000, false])]
    #[TestWith([8_640_000_000_000, 360_000_000_000, true])]
    #[TestWith([360_000_000_000, 8_640_000_000_000, false])]
    public function testGreaterThan(int $firstMicroseconds, int $secondMicroseconds, bool $expected): void
    {
        $first = TimeSpan::fromMicroseconds($firstMicroseconds);
        $second = TimeSpan::fromMicroseconds($secondMicroseconds);

        self::assertSame($expected, $first->isGreaterThan($second));
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000, true])]
    #[TestWith([8_640_000_000_000, 360_000_000_000, true])]
    #[TestWith([360_000_000_000, 8_640_000_000_000, false])]
    public function testIsGreaterThanOrEqualTo(int $firstMicroseconds, int $secondMicroseconds, bool $expected): void
    {
        $first = TimeSpan::fromMicroseconds($firstMicroseconds);
        $second = TimeSpan::fromMicroseconds($secondMicroseconds);

        self::assertSame($expected, $first->isGreaterThanOrEqualTo($second));
    }

    #[TestWith([-8_640_000_000_000, false])]
    #[TestWith([8_640_000_000_000, false])]
    #[TestWith([0, true])]
    public function testIsZero(int $microseconds, bool $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expected, $span->isZero());
    }

    #[TestWith([-8_640_000_000_000, true])]
    #[TestWith([8_640_000_000_000, false])]
    #[TestWith([0, false])]
    public function testIsNegative(int $microseconds, bool $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expected, $span->isNegative());
    }

    #[TestWith([-8_640_000_000_000, true])]
    #[TestWith([8_640_000_000_000, false])]
    #[TestWith([0, true])]
    public function testIsNegativeOrZero(int $microseconds, bool $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expected, $span->isNegativeOrZero());
    }

    #[TestWith([-8_640_000_000_000, false])]
    #[TestWith([8_640_000_000_000, true])]
    #[TestWith([0, false])]
    public function testIsPositive(int $microseconds, bool $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expected, $span->isPositive());
    }

    #[TestWith([-8_640_000_000_000, false])]
    #[TestWith([8_640_000_000_000, true])]
    #[TestWith([0, true])]
    public function testIsPositiveOrZero(int $microseconds, bool $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expected, $span->isPositiveOrZero());
    }
}
