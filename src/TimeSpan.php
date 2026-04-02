<?php

declare(strict_types=1);

namespace Thesis;

use Thesis\TimeSpan\Internal\Unit;
use function Thesis\TimeSpan\Internal\calculateFormatReplacements;
use function Thesis\TimeSpan\Internal\isCastableToInt;

/**
 * @api
 *
 * @final Do not extend this class, it will be final in 0.3.0
 */
readonly class TimeSpan
{
    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function from(
        int|float $days = 0,
        int|float $hours = 0,
        int|float $minutes = 0,
        int|float $seconds = 0,
        int|float $milliseconds = 0,
        int|float $microseconds = 0,
        int|float $nanoseconds = 0,
    ): self {
        return self::fromNanoseconds(
            $days * Unit::DAYS
            + $hours * Unit::HOURS
            + $minutes * Unit::MINUTES
            + $seconds * Unit::SECONDS
            + $milliseconds * Unit::MILLISECONDS
            + $microseconds * Unit::MICROSECONDS
            + $nanoseconds,
        );
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromNanoseconds(int|float $nanoseconds): self
    {
        if (\is_int($nanoseconds)) {
            return new self($nanoseconds);
        }

        $nanoseconds = round($nanoseconds);

        if (isCastableToInt($nanoseconds)) {
            return new self((int) $nanoseconds);
        }

        throw new \OverflowException('The specified time span cannot be expressed as integer nanoseconds due to overflow');
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromMicroseconds(int|float $microseconds): self
    {
        return self::fromNanoseconds($microseconds * Unit::MICROSECONDS);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromMilliseconds(int|float $milliseconds): self
    {
        return self::fromNanoseconds($milliseconds * Unit::MILLISECONDS);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromSeconds(int|float $seconds): self
    {
        return self::fromNanoseconds($seconds * Unit::SECONDS);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromMinutes(int|float $minutes): self
    {
        return self::fromNanoseconds($minutes * Unit::MINUTES);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromHours(int|float $hours): self
    {
        return self::fromNanoseconds($hours * Unit::HOURS);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromDays(int|float $days): self
    {
        return self::fromNanoseconds($days * Unit::DAYS);
    }

    public static function between(\DateTimeImmutable $a, \DateTimeImmutable $b): self
    {
        return self::from(
            seconds: (int) $a->format('U') - (int) $b->format('U'),
            microseconds: (int) $a->format('u') - (int) $b->format('u'),
        );
    }

    /**
     * @deprecated use {@see self::between()} instead
     */
    public static function diff(\DateTimeImmutable $a, \DateTimeImmutable $b): self
    {
        return self::between($a, $b);
    }

    public static function hrtime(): self
    {
        return self::fromNanoseconds(hrtime(true));
    }

    /**
     * @throws \InvalidArgumentException if the interval contains months or years
     * @throws \InvalidArgumentException if the interval was obtained from {@see \DateTimeInterface::diff()}
     */
    public static function fromInterval(\DateInterval $interval): self
    {
        if ($interval->m !== 0 || $interval->y !== 0) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Month and year cannot be converted to nanoseconds correctly. Use `%s::between()` instead.',
                    self::class,
                ),
            );
        }

        if ($interval->days !== false) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Given interval was obtained from `%s::diff()` and cannot be interpreted correctly due to DST changeovers. Use `%s::between()` instead.',
                    \DateTimeInterface::class,
                    self::class,
                ),
            );
        }

        $timeSpan = self::from(
            days: $interval->d,
            hours: $interval->h,
            minutes: $interval->i,
            seconds: $interval->s + $interval->f,
        );

        if ($interval->invert === 1) {
            return new self(-$timeSpan->nanoseconds);
        }

        return $timeSpan;
    }

    public function __construct(
        private int $nanoseconds = 0,
    ) {}

    public function toNanoseconds(): int
    {
        return $this->nanoseconds;
    }

    /**
     * @param int<1, 4> $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toMicroseconds(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(Unit::MICROSECONDS, $precision, $roundingMode);
    }

    /**
     * @param int<1, 4> $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toMilliseconds(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(Unit::MILLISECONDS, $precision, $roundingMode);
    }

    /**
     * @param int<1, 4> $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toSeconds(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(Unit::SECONDS, $precision, $roundingMode);
    }

    /**
     * @param int<1, 4> $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toMinutes(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(Unit::MINUTES, $precision, $roundingMode);
    }

    /**
     * @param int<1, 4> $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toHours(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(Unit::HOURS, $precision, $roundingMode);
    }

    /**
     * @param int<1, 4> $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toDays(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(Unit::DAYS, $precision, $roundingMode);
    }

    /**
     * @param int<1, 4> $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    private function toX(int $multiplier, int $precision, int $roundingMode): int|float
    {
        $value = round(
            num: $this->nanoseconds / $multiplier,
            precision: $precision,
            mode: $roundingMode,
        );

        if ($precision <= 0) {
            return (int) $value;
        }

        return $value;
    }

    public function abs(): self
    {
        return new self(abs($this->nanoseconds));
    }

    public function negated(): self
    {
        return new self(-$this->nanoseconds);
    }

    /**
     * @return int<-1, 1>
     */
    public function compareTo(self $another): int
    {
        return $this->nanoseconds <=> $another->nanoseconds;
    }

    public function isEqualTo(self $another): bool
    {
        return $this->nanoseconds === $another->nanoseconds;
    }

    public function isLessThan(self $another): bool
    {
        return $this->nanoseconds < $another->nanoseconds;
    }

    public function isLessThanOrEqualTo(self $another): bool
    {
        return $this->nanoseconds <= $another->nanoseconds;
    }

    public function isGreaterThanOrEqualTo(self $another): bool
    {
        return $this->nanoseconds >= $another->nanoseconds;
    }

    public function isGreaterThan(self $another): bool
    {
        return $this->nanoseconds > $another->nanoseconds;
    }

    public function isNegative(): bool
    {
        return $this->nanoseconds < 0;
    }

    public function isNegativeOrZero(): bool
    {
        return $this->nanoseconds <= 0;
    }

    public function isZero(): bool
    {
        return $this->nanoseconds === 0;
    }

    public function isPositiveOrZero(): bool
    {
        return $this->nanoseconds >= 0;
    }

    public function isPositive(): bool
    {
        return $this->nanoseconds > 0;
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public function add(self $timeSpan): self
    {
        return self::fromNanoseconds($this->nanoseconds + $timeSpan->nanoseconds);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public function sub(self $timeSpan): self
    {
        return self::fromNanoseconds($this->nanoseconds - $timeSpan->nanoseconds);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public function mul(int|float $times): self
    {
        return self::fromNanoseconds($this->nanoseconds * $times);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     * @throws \DivisionByZeroError
     */
    public function div(int|float $factor): self
    {
        return self::fromNanoseconds($this->nanoseconds / $factor);
    }

    /**
     * Formats the span using placeholders:
     *
     *   %-  sign: `-` for negative spans, empty string otherwise
     *   %d  days (no padding)
     *   %h  hours (zero-padded to 2)
     *   %i  minutes (zero-padded to 2)
     *   %s  seconds (zero-padded to 2)
     *   %ms milliseconds (zero-padded to 3)
     *   %us microseconds (zero-padded to 3)
     *   %ns nanoseconds (zero-padded to 3)
     *
     * The largest unit present receives the total cumulative value;
     * each smaller unit shows the remainder after the larger ones are subtracted.
     * Placeholders may appear multiple times or not at all.
     */
    public function format(string $format = '%-%h:%i:%s'): string
    {
        return strtr($format, calculateFormatReplacements($format, $this->nanoseconds));
    }

    /**
     * @return non-empty-string
     */
    public function __toString(): string
    {
        /** @var non-empty-string */
        return $this->format();
    }
}
