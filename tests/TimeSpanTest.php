<?php

declare(strict_types=1);

namespace Thesis\Time;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeSpan::class)]
final class TimeSpanTest extends TestCase
{
    #[TestWith(['@123.00333', '@124.00555', -1_002_220])]
    #[TestWith(['@124.00555', '@123.00333', 1_002_220])]
    #[TestWith(['2021-10-31 08:30:00 Europe/London', '2021-10-30 09:00:00 Europe/London', 88_200_000_000])]
    #[TestWith(['2021-10-31 09:00:00 Europe/London', '2021-10-30 09:00:00 Europe/London', 90_000_000_000])]
    public function testDiff(string $a, string $b, int $expectedDiffUs): void
    {
        $diff = TimeSpan::diff(new \DateTimeImmutable($a), new \DateTimeImmutable($b));

        self::assertSame($expectedDiffUs, $diff->toMicroseconds());
    }

    #[DataProviderExternal(FormatsDataProvider::class, 'formats')]
    public function testFormat(string $format, int $nanoseconds, string $expected): void
    {
        $timeSpan = TimeSpan::fromNanoseconds($nanoseconds);

        $timeSpanFormated = $timeSpan->format($format);

        self::assertSame($expected, $timeSpanFormated);
    }

    public function testItThrowsForInvalidFormatWithOutPlaceholders(): void
    {
        $format = 'some text without placeholders';
        $timeSpan = TimeSpan::fromNanoseconds(123);

        $this->expectExceptionObject(
            new \InvalidArgumentException(
                \sprintf(
                    'Given format `%s` is not valid. Available units: `%%d`, `%%h`, `%%i`, `%%s`, `%%ms`, `%%us`, `%%ns`',
                    $format,
                ),
            ),
        );

        $timeSpan->format($format);
    }

    public function testItThrowsForInvalidFormatWithRepeatingPlaceholder(): void
    {
        $format = '%d %h:%i:%s.%ms_%us_%ns | %h:%i:%s.%ns';
        $repeatingPlaceholder = '%h';
        $timeSpan = TimeSpan::fromNanoseconds(123);

        $this->expectExceptionObject(
            new \InvalidArgumentException(
                \sprintf(
                    'Given format `%s` contains more than one `%s` placeholder',
                    $format,
                    $repeatingPlaceholder,
                ),
            ),
        );

        $timeSpan->format($format);
    }

    /**
     * @param array{days?: float|int, hours?: float|int, minutes?: float|int, seconds?: float|int, milliseconds?: float|int, microseconds?: float|int} $args
     */
    #[TestWith([
        [],
        0,
    ])]
    #[TestWith([
        ['seconds' => 987, 'milliseconds' => 654, 'microseconds' => 321, 'nanoseconds' => 123],
        987_654_321_123,
    ])]
    #[TestWith([
        ['milliseconds' => -1.555, 'microseconds' => -445, 'nanoseconds' => -123],
        -2_000_123,
    ])]
    #[TestWith([
        ['days' => 7, 'hours' => 12, 'minutes' => 49, 'seconds' => 35, 'milliseconds' => 222, 'microseconds' => 333, 'nanoseconds' => 123],
        650_975_222_333_123,
    ])]
    #[TestWith([
        ['days' => 7, 'hours' => 12, 'minutes' => 49, 'seconds' => 35, 'milliseconds' => 222, 'nanoseconds' => 10_000],
        650_975_222_010_000,
    ])]
    #[TestWith([
        ['days' => 7, 'hours' => 12, 'minutes' => 49, 'seconds' => 35, 'milliseconds' => 222],
        650_975_222_000_000,
    ])]
    #[TestWith([
        ['days' => 7, 'hours' => 12, 'minutes' => 49, 'seconds' => 35],
        650_975_000_000_000,
    ])]
    #[TestWith([
        ['days' => 7, 'hours' => 12, 'minutes' => 49],
        650_940_000_000_000,
    ])]
    #[TestWith([
        ['days' => 7, 'hours' => 12],
        648_000_000_000_000,
    ])]
    #[TestWith([
        ['days' => 7],
        604_800_000_000_000,
    ])]
    public function testFrom(array $args, int $expected): void
    {
        $timeSpan = TimeSpan::from(...$args);

        self::assertSame($expected, $timeSpan->toNanoseconds());
    }

    #[TestWith(['P1W2D', 777_600_000_000])]
    #[TestWith(['P7D', 604_800_000_000])]
    #[TestWith(['PT2S', 2_000_000])]
    public function testFromInterval(string $interval, int $expected): void
    {
        $timeSpan = TimeSpan::fromInterval(new \DateInterval($interval));

        self::assertSame($expected, $timeSpan->toMicroseconds());
    }

    #[TestWith(['P1W2D', -777_600_000_000])]
    #[TestWith(['P7D', -604_800_000_000])]
    #[TestWith(['PT2S', -2_000_000])]
    public function testFromIntervalInvert(string $interval, int $expected): void
    {
        $dateInterval = new \DateInterval($interval);
        $dateInterval->invert = 1;

        $timeSpan = TimeSpan::fromInterval($dateInterval);

        self::assertSame($expected, $timeSpan->toMicroseconds());
    }

    #[TestWith(['P1Y2M'])]
    #[TestWith(['P1Y'])]
    #[TestWith(['P2M'])]
    public function testItThrowsForInvalidInterval(string $interval): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException(
                \sprintf(
                    'Month and year cannot be converted to nanoseconds correctly. Use `%s::diff()` instead.',
                    TimeSpan::class,
                ),
            ),
        );

        TimeSpan::fromInterval(new \DateInterval($interval));
    }

    public function testItThrowsForDateTimeInterfaceDiffInterval(): void
    {
        $originalTime = new \DateTimeImmutable('2021-10-30 09:00:00 Europe/London');
        $targetTime = new \DateTimeImmutable('2021-10-31 09:00:00 Europe/London');
        $interval = $originalTime->diff($targetTime);

        $this->expectExceptionObject(
            new \InvalidArgumentException(
                \sprintf(
                    'Given interval was obtained from `%s::diff()` and cannot be interpreted correctly due to DST changeovers. Use `%s::diff()` instead.',
                    \DateTimeInterface::class,
                    TimeSpan::class,
                ),
            ),
        );

        TimeSpan::fromInterval($interval);
    }

    #[TestWith([0])]
    #[TestWith([100])]
    #[TestWith([-100])]
    #[TestWith([PHP_INT_MAX])]
    #[TestWith([PHP_INT_MIN])]
    #[TestWith([9_223_372_036_854_775_000])]
    #[TestWith([-9_223_372_036_854_775_000])]
    public function testConstructor(int $nanoseconds): void
    {
        $span = new TimeSpan($nanoseconds);

        self::assertSame($nanoseconds, $span->toNanoseconds());
    }

    public function testConstructorWithoutArgs(): void
    {
        $span = new TimeSpan();

        self::assertSame(0, $span->toNanoseconds());
    }

    #[TestWith([0, 0])]
    #[TestWith([0.0, 0])]
    #[TestWith([100, 100])]
    #[TestWith([100.1, 100])]
    #[TestWith([100.5, 101])]
    #[TestWith([100.999_99, 101])]
    #[TestWith([PHP_INT_MAX, PHP_INT_MAX])]
    #[TestWith([PHP_INT_MIN, PHP_INT_MIN])]
    #[TestWith([9_223_372_036_854_775_000.0, 9_223_372_036_854_774_784])]
    #[TestWith([-9_223_372_036_854_775_000.0, -9_223_372_036_854_774_784])]
    public function testFromNanoseconds(int|float $nanoseconds, int $expected): void
    {
        $span = TimeSpan::fromNanoseconds($nanoseconds);

        self::assertSame($expected, $span->toNanoseconds());
    }

    #[TestWith([0, 0])]
    #[TestWith([0.0, 0])]
    #[TestWith([100, 100])]
    #[TestWith([100.1, 100])]
    #[TestWith([100.5, 101])]
    #[TestWith([100.999_99, 101])]
    public function testFromMicroseconds(int|float $microseconds, int $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        self::assertSame($expected, $span->toMicroseconds());
    }

    #[TestWith([0, 0])]
    #[TestWith([0.0, 0])]
    #[TestWith([100, 100_000])]
    #[TestWith([100.1, 100_100])]
    #[TestWith([100.5, 100_500])]
    #[TestWith([100.999_99, 101_000])]
    public function testFromMilliseconds(int|float $milliseconds, int $expected): void
    {
        $span = TimeSpan::fromMilliseconds($milliseconds);

        self::assertSame($expected, $span->toMicroseconds());
    }

    #[TestWith([0, 0])]
    #[TestWith([0.0, 0])]
    #[TestWith([100, 100_000_000])]
    #[TestWith([100.1, 100_100_000])]
    #[TestWith([100.5, 100_500_000])]
    #[TestWith([100.999_99, 100_999_990])]
    public function testFromSeconds(int|float $seconds, int $expected): void
    {
        $span = TimeSpan::fromSeconds($seconds);

        self::assertSame($expected, $span->toMicroseconds());
    }

    #[TestWith([0, 0])]
    #[TestWith([0.0, 0])]
    #[TestWith([100, 6_000_000_000])]
    #[TestWith([100.1, 6_006_000_000])]
    #[TestWith([100.5, 6_030_000_000])]
    #[TestWith([100.999_99, 6_059_999_400])]
    public function testFromMinutes(int|float $minutes, int $expected): void
    {
        $span = TimeSpan::fromMinutes($minutes);

        self::assertSame($expected, $span->toMicroseconds());
    }

    #[TestWith([0, 0])]
    #[TestWith([0.0, 0])]
    #[TestWith([100, 360_000_000_000])]
    #[TestWith([100.1, 360_360_000_000])]
    #[TestWith([100.5, 361_800_000_000])]
    #[TestWith([100.999_99, 363_599_964_000])]
    public function testFromHours(int|float $hours, int $expected): void
    {
        $span = TimeSpan::fromHours($hours);

        self::assertSame($expected, $span->toMicroseconds());
    }

    #[TestWith([0, 0])]
    #[TestWith([0.0, 0])]
    #[TestWith([100, 8_640_000_000_000_000])]
    #[TestWith([106_751, 9_223_286_400_000_000_000])]
    #[TestWith([-106_751, -9_223_286_400_000_000_000])]
    #[TestWith([106_751.991_167_300_628_148_950_636_386_871_337_890_625, 9_223_372_036_854_774_784])]
    #[TestWith([-106_751.991_167_300_628_148_950_636_386_871_337_890_625, -9_223_372_036_854_774_784])]
    public function testFromDays(int|float $days, int $expected): void
    {
        $span = TimeSpan::fromDays($days);

        self::assertSame($expected, $span->toNanoseconds());
    }

    #[RequiresPhp('<8.4')]
    #[TestWith([100.1, 8_648_640_000_000_000])]
    #[TestWith([100.5, 8_683_200_000_000_000])]
    #[TestWith([100.999_99, 8_726_399_136_000_000])]
    public function testFromDaysPHPBefore84(float $days, int $expected): void
    {
        $span = TimeSpan::fromDays($days);

        self::assertSame($expected, $span->toNanoseconds());
    }

    #[RequiresPhp('>=8.4')]
    #[TestWith([100.1, 8_648_640_000_000_001])]
    #[TestWith([100.5, 8_683_200_000_000_001])]
    #[TestWith([100.999_99, 8_726_399_136_000_001])]
    public function testFromDaysPHPSince84(float $days, int $expected): void
    {
        $span = TimeSpan::fromDays($days);

        self::assertSame($expected, $span->toNanoseconds());
    }

    #[TestWith([106_752])]
    #[TestWith([106_751.991_2])]
    #[TestWith([-106_752])]
    #[TestWith([-106_751.991_2])]
    public function testFromDaysThrowsOutOfBounds(int|float $days): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('The specified time span cannot be expressed as integer nanoseconds due to overflow.');

        TimeSpan::fromDays($days);
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
    public function testAbs(int $microseconds, int $expectedAbs): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        $abs = $span->abs()->toMicroseconds();

        self::assertSame($expectedAbs, $abs);
    }

    #[TestWith([8_640_000_000_000, -8_640_000_000_000])]
    #[TestWith([-8_640_000_000_000, 8_640_000_000_000])]
    public function testNegated(int $microseconds, int $expectedNegated): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        $negated = $span->negated()->toMicroseconds();

        self::assertSame($expectedNegated, $negated);
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000, 0])]
    #[TestWith([8_640_000_000_000, 360_000_000_000, 1])]
    #[TestWith([360_000_000_000, 8_640_000_000_000, -1])]
    public function testCompareTo(int $firstMicroseconds, int $secondMicroseconds, int $expectedComparison): void
    {
        $first = TimeSpan::fromMicroseconds($firstMicroseconds);
        $second = TimeSpan::fromMicroseconds($secondMicroseconds);

        $comparison = $first->compareTo($second);

        self::assertSame($expectedComparison, $comparison);
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000, true])]
    #[TestWith([8_640_000_000_000, 360_000_000_000, false])]
    #[TestWith([360_000_000_000, 8_640_000_000_000, false])]
    public function testIsEqualTo(int $firstMicroseconds, int $secondMicroseconds, bool $expected): void
    {
        $first = TimeSpan::fromMicroseconds($firstMicroseconds);
        $second = TimeSpan::fromMicroseconds($secondMicroseconds);

        $equals = $first->isEqualTo($second);

        self::assertSame($expected, $equals);
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000, false])]
    #[TestWith([8_640_000_000_000, 360_000_000_000, false])]
    #[TestWith([360_000_000_000, 8_640_000_000_000, true])]
    public function testLessThan(int $firstMicroseconds, int $secondMicroseconds, bool $expected): void
    {
        $first = TimeSpan::fromMicroseconds($firstMicroseconds);
        $second = TimeSpan::fromMicroseconds($secondMicroseconds);

        $actual = $first->isLessThan($second);

        self::assertSame($expected, $actual);
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000, true])]
    #[TestWith([8_640_000_000_000, 360_000_000_000, false])]
    #[TestWith([360_000_000_000, 8_640_000_000_000, true])]
    public function testIsLessThanOrEqualTo(int $firstMicroseconds, int $secondMicroseconds, bool $expected): void
    {
        $first = TimeSpan::fromMicroseconds($firstMicroseconds);
        $second = TimeSpan::fromMicroseconds($secondMicroseconds);

        $actual = $first->isLessThanOrEqualTo($second);

        self::assertSame($expected, $actual);
    }

    #[TestWith([-8_640_000_000_000, false])]
    #[TestWith([8_640_000_000_000, false])]
    #[TestWith([0, true])]
    public function testIsZero(int $microseconds, bool $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        $actual = $span->isZero();

        self::assertSame($expected, $actual);
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000, true])]
    #[TestWith([8_640_000_000_000, 360_000_000_000, true])]
    #[TestWith([360_000_000_000, 8_640_000_000_000, false])]
    public function testIsGreaterThanOrEqualTo(int $firstMicroseconds, int $secondMicroseconds, bool $expected): void
    {
        $first = TimeSpan::fromMicroseconds($firstMicroseconds);
        $second = TimeSpan::fromMicroseconds($secondMicroseconds);

        $actual = $first->isGreaterThanOrEqualTo($second);

        self::assertSame($expected, $actual);
    }

    #[TestWith([8_640_000_000_000, 8_640_000_000_000, false])]
    #[TestWith([8_640_000_000_000, 360_000_000_000, true])]
    #[TestWith([360_000_000_000, 8_640_000_000_000, false])]
    public function testGreaterThan(int $firstMicroseconds, int $secondMicroseconds, bool $expected): void
    {
        $first = TimeSpan::fromMicroseconds($firstMicroseconds);
        $second = TimeSpan::fromMicroseconds($secondMicroseconds);

        $actual = $first->isGreaterThan($second);

        self::assertSame($expected, $actual);
    }

    #[TestWith([-8_640_000_000_000, true])]
    #[TestWith([8_640_000_000_000, false])]
    #[TestWith([0, false])]
    public function testIsNegative(int $microseconds, bool $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        $actual = $span->isNegative();

        self::assertSame($expected, $actual);
    }

    #[TestWith([-8_640_000_000_000, true])]
    #[TestWith([8_640_000_000_000, false])]
    #[TestWith([0, true])]
    public function testIsNegativeOrZero(int $microseconds, bool $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        $actual = $span->isNegativeOrZero();

        self::assertSame($expected, $actual);
    }

    #[TestWith([-8_640_000_000_000, false])]
    #[TestWith([8_640_000_000_000, true])]
    #[TestWith([0, false])]
    public function testIsPositive(int $microseconds, bool $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        $actual = $span->isPositive();

        self::assertSame($expected, $actual);
    }

    #[TestWith([-8_640_000_000_000, false])]
    #[TestWith([8_640_000_000_000, true])]
    #[TestWith([0, true])]
    public function testIsPositiveOrZero(int $microseconds, bool $expected): void
    {
        $span = TimeSpan::fromMicroseconds($microseconds);

        $actual = $span->isPositiveOrZero();

        self::assertSame($expected, $actual);
    }

    #[TestWith([2, 2, 4])]
    #[TestWith([250, 250, 500])]
    #[TestWith([-250, 250, 0])]
    #[TestWith([-250, -250, -500])]
    public function testAdd(int $firstDays, int $secondDays, int $sumDays): void
    {
        $firstSpan = TimeSpan::fromDays($firstDays);
        $secondSpan = TimeSpan::fromDays($secondDays);

        $sum = $firstSpan->add($secondSpan);

        self::assertEquals($sumDays, $sum->toDays());
    }

    public function testAddOverflow(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $tooManyDays = 99_999;
        $firstSpan = TimeSpan::fromDays($tooManyDays);
        $secondSpan = TimeSpan::fromDays($tooManyDays);

        $firstSpan->add($secondSpan);
    }

    #[TestWith([4, 2, 2])]
    #[TestWith([250, 250, 0])]
    #[TestWith([-250, 250, -500])]
    #[TestWith([-250, -250, 0])]
    public function testSub(int $firstDays, int $secondDays, int $diffDays): void
    {
        $firstSpan = TimeSpan::fromDays($firstDays);
        $secondSpan = TimeSpan::fromDays($secondDays);

        $diff = $firstSpan->sub($secondSpan);

        self::assertEquals($diffDays, $diff->toDays());
    }

    public function testSubOverflow(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $tooManyDays = 99_999;
        $zeroSpan = TimeSpan::fromDays(0);
        $subSpan = TimeSpan::fromDays($tooManyDays);

        $zeroSpan->sub($subSpan)
            ->sub($subSpan);
    }

    #[TestWith([2, 2, 4])]
    #[TestWith([3, 3, 9])]
    #[TestWith([5, 0, 0])]
    #[TestWith([10, 0.5, 5])]
    public function testMul(int $days, int|float $times, int $daysProduct): void
    {
        $span = TimeSpan::fromDays($days);

        $product = $span->mul($times);

        self::assertEquals($daysProduct, $product->toDays());
    }

    public function testMulOverflow(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $tooManyDays = 99_999;
        $span = TimeSpan::fromDays($tooManyDays);

        $span->mul(2);
    }

    #[TestWith([8_640_000_000_000, 1_000, 8_640_000_000])]
    #[TestWith([1_111_111_111_111, 3, 370_370_370_370])]
    #[TestWith([1_111_111_111_111, 4, 277_777_777_778])]
    #[TestWith([864_000_000, 0.1, 8_640_000_000])]
    public function testDiv(int $nanoseconds, int|float $factor, int $nanosecondsQuotient): void
    {
        $span = TimeSpan::fromNanoseconds($nanoseconds);

        $quotient = $span->div($factor);

        self::assertEquals($nanosecondsQuotient, $quotient->toNanoseconds());
    }

    public function testDivOverflow(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $tooManyDays = 99_999;
        $span = TimeSpan::fromDays($tooManyDays);

        $span->div(0.1);
    }

    public function testDivByZero(): void
    {
        $this->expectException(\DivisionByZeroError::class);

        $span = TimeSpan::fromDays(5);
        $span->div(0);
    }
}
