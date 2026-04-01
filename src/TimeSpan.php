<?php

declare(strict_types=1);

namespace Thesis\Time;

/**
 * @api
 */
final readonly class TimeSpan
{
    private const int MULT_NANOSECONDS = 1;
    private const int MULT_MICROSECONDS = self::MULT_NANOSECONDS * 1_000;
    private const int MULT_MILLISECONDS = self::MULT_MICROSECONDS * 1_000;
    private const int MULT_SECONDS = self::MULT_MILLISECONDS * 1_000;
    private const int MULT_MINUTES = self::MULT_SECONDS * 60;
    private const int MULT_HOURS = self::MULT_MINUTES * 60;
    private const int MULT_DAYS = self::MULT_HOURS * 24;
    private const float BOUND = 2 ** 63;

    public static function diff(\DateTimeImmutable $a, \DateTimeImmutable $b): self
    {
        return self::from(
            seconds: (int) $a->format('U') - (int) $b->format('U'),
            microseconds: (int) $a->format('u') - (int) $b->format('u'),
        );
    }

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
            $days * self::MULT_DAYS
            + $hours * self::MULT_HOURS
            + $minutes * self::MULT_MINUTES
            + $seconds * self::MULT_SECONDS
            + $milliseconds * self::MULT_MILLISECONDS
            + $microseconds * self::MULT_MICROSECONDS
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

        if (self::isOutOfBounds($nanoseconds)) {
            throw new \OverflowException('The specified time span cannot be expressed as integer nanoseconds due to overflow.');
        }

        return new self((int) $nanoseconds);
    }

    private static function isOutOfBounds(float $nanoseconds): bool
    {
        return !is_finite($nanoseconds)
            || $nanoseconds >= self::BOUND
            || $nanoseconds < -self::BOUND;
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromMicroseconds(int|float $microseconds): self
    {
        return self::fromNanoseconds($microseconds * self::MULT_MICROSECONDS);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromMilliseconds(int|float $milliseconds): self
    {
        return self::fromNanoseconds($milliseconds * self::MULT_MILLISECONDS);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromSeconds(int|float $seconds): self
    {
        return self::fromNanoseconds($seconds * self::MULT_SECONDS);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromMinutes(int|float $minutes): self
    {
        return self::fromNanoseconds($minutes * self::MULT_MINUTES);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromHours(int|float $hours): self
    {
        return self::fromNanoseconds($hours * self::MULT_HOURS);
    }

    /**
     * @throws \OverflowException if the value exceeds the int64 range
     */
    public static function fromDays(int|float $days): self
    {
        return self::fromNanoseconds($days * self::MULT_DAYS);
    }

    public static function fromInterval(\DateInterval $interval): self
    {
        if ($interval->m !== 0 || $interval->y !== 0) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Month and year cannot be converted to nanoseconds correctly. Use `%s::diff()` instead.',
                    self::class,
                ),
            );
        }

        if ($interval->days !== false) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Given interval was obtained from `%s::diff()` and cannot be interpreted correctly due to DST changeovers. Use `%s::diff()` instead.',
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
        return $this->toX(self::MULT_MICROSECONDS, $precision, $roundingMode);
    }

    /**
     * @param int<1, 4> $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toMilliseconds(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(self::MULT_MILLISECONDS, $precision, $roundingMode);
    }

    /**
     * @param int<1, 4> $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toSeconds(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(self::MULT_SECONDS, $precision, $roundingMode);
    }

    /**
     * @param int<1, 4> $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toMinutes(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(self::MULT_MINUTES, $precision, $roundingMode);
    }

    /**
     * @param int<1, 4> $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toHours(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(self::MULT_HOURS, $precision, $roundingMode);
    }

    /**
     * @param int<1, 4> $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toDays(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(self::MULT_DAYS, $precision, $roundingMode);
    }

    /**
     * @param self::MULT_* $multiplier
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

    public function isZero(): bool
    {
        return $this->nanoseconds === 0;
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

    public function isPositive(): bool
    {
        return $this->nanoseconds > 0;
    }

    public function isPositiveOrZero(): bool
    {
        return $this->nanoseconds >= 0;
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
     */
    public function div(int|float $factor): self
    {
        return self::fromNanoseconds($this->nanoseconds / $factor);
    }
    private const array UNITS_MULT_MAP = [
        'd' => self::MULT_DAYS,
        'h' => self::MULT_HOURS,
        'i' => self::MULT_MINUTES,
        's' => self::MULT_SECONDS,
        'ms' => self::MULT_MILLISECONDS,
        'us' => self::MULT_MICROSECONDS,
        'ns' => self::MULT_NANOSECONDS,
    ];
    private const array UNIT_PLACEHOLDERS = [
        'd' => '%d',
        'h' => '%h',
        'i' => '%i',
        's' => '%s',
        'ms' => '%ms',
        'us' => '%us',
        'ns' => '%ns',
    ];

    public function format(string $format = '%h:%i:%s'): string
    {
        $usedUnits = array_filter(self::UNIT_PLACEHOLDERS, static fn(string $unit): bool => str_contains($format, $unit));

        if ($usedUnits === []) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Given format `%s` is not valid. Available units: %s',
                    $format,
                    implode(', ', array_map(static fn(string $unit): string => "`{$unit}`", self::UNIT_PLACEHOLDERS)),
                ),
            );
        }

        foreach ($usedUnits as $placeholder) {
            if (substr_count($format, $placeholder) > 1) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Given format `%s` contains more than one `%s` placeholder',
                        $format,
                        $placeholder,
                    ),
                );
            }
        }

        $remaining = abs($this->nanoseconds);
        $result = $format;

        foreach ($usedUnits as $unit => $placeholder) {
            $value = (int) floor($remaining / self::UNITS_MULT_MAP[$unit]);
            $formattedValue = match ($unit) {
                'd' => (string) $value,
                'h', 'i', 's' => str_pad((string) $value, 2, '0', STR_PAD_LEFT),
                'ms', 'us', 'ns' => str_pad((string) $value, 3, '0', STR_PAD_LEFT),
            };
            $remaining %= self::UNITS_MULT_MAP[$unit];

            $result = str_replace($placeholder, $formattedValue, $result);
        }

        if ($this->nanoseconds < 0) {
            return '-' . $result;
        }

        return $result;
    }
}
