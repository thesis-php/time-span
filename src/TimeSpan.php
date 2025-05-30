<?php

declare(strict_types=1);

namespace Thesis\Time;

/**
 * @api
 */
final readonly class TimeSpan
{
    private const int MULT_NANOSECONDS = 1;
    private const int MULT_MICROSECONDS = self::MULT_NANOSECONDS * 1000;
    private const int MULT_MILLISECONDS = self::MULT_MICROSECONDS * 1000;
    private const int MULT_SECONDS = self::MULT_MILLISECONDS * 1000;
    private const int MULT_MINUTES = self::MULT_SECONDS * 60;
    private const int MULT_HOURS = self::MULT_MINUTES * 60;
    private const int MULT_DAYS = self::MULT_HOURS * 24;

    public static function diff(\DateTimeImmutable $a, \DateTimeImmutable $b): self
    {
        return self::fromMicroseconds((int) $a->format('Uu') - (int) $b->format('Uu'));
    }

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

    public static function fromNanoseconds(int|float $nanoseconds): self
    {
        return new self(self::fromX($nanoseconds, self::MULT_NANOSECONDS));
    }

    public static function fromMicroseconds(int|float $microseconds): self
    {
        return new self(self::fromX($microseconds, self::MULT_MICROSECONDS));
    }

    public static function fromMilliseconds(int|float $milliseconds): self
    {
        return new self(self::fromX($milliseconds, self::MULT_MILLISECONDS));
    }

    public static function fromSeconds(int|float $seconds): self
    {
        return new self(self::fromX($seconds, self::MULT_SECONDS));
    }

    public static function fromMinutes(int|float $minutes): self
    {
        return new self(self::fromX($minutes, self::MULT_MINUTES));
    }

    public static function fromHours(int|float $hours): self
    {
        return new self(self::fromX($hours, self::MULT_HOURS));
    }

    public static function fromDays(int|float $days): self
    {
        return new self(self::fromX($days, self::MULT_DAYS));
    }

    /**
     * @param self::MULT_* $multiplier
     */
    private static function fromX(int|float $value, int $multiplier): int
    {
        if (\is_int($value)) {
            return $value * $multiplier;
        }

        return (int) round($value * $multiplier);
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
            seconds: $interval->s,
            microseconds: $interval->f,
        );

        if ($interval->invert === 1) {
            return new self(-$timeSpan->nanoseconds);
        }

        return $timeSpan;
    }

    private function __construct(
        private int $nanoseconds,
    ) {}

    public function toNanoseconds(): int
    {
        return $this->nanoseconds;
    }

    /**
     * @phpstan-param PHP_ROUND_HALF_UP|PHP_ROUND_HALF_DOWN|PHP_ROUND_HALF_EVEN|PHP_ROUND_HALF_ODD $roundingMode
     * @psalm-param positive-int $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toMicroseconds(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(self::MULT_MICROSECONDS, $precision, $roundingMode);
    }

    /**
     * @phpstan-param PHP_ROUND_HALF_UP|PHP_ROUND_HALF_DOWN|PHP_ROUND_HALF_EVEN|PHP_ROUND_HALF_ODD $roundingMode
     * @psalm-param positive-int $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toMilliseconds(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(self::MULT_MILLISECONDS, $precision, $roundingMode);
    }

    /**
     * @phpstan-param PHP_ROUND_HALF_UP|PHP_ROUND_HALF_DOWN|PHP_ROUND_HALF_EVEN|PHP_ROUND_HALF_ODD $roundingMode
     * @psalm-param positive-int $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toSeconds(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(self::MULT_SECONDS, $precision, $roundingMode);
    }

    /**
     * @phpstan-param PHP_ROUND_HALF_UP|PHP_ROUND_HALF_DOWN|PHP_ROUND_HALF_EVEN|PHP_ROUND_HALF_ODD $roundingMode
     * @psalm-param positive-int $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toMinutes(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(self::MULT_MINUTES, $precision, $roundingMode);
    }

    /**
     * @phpstan-param PHP_ROUND_HALF_UP|PHP_ROUND_HALF_DOWN|PHP_ROUND_HALF_EVEN|PHP_ROUND_HALF_ODD $roundingMode
     * @psalm-param positive-int $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toHours(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(self::MULT_HOURS, $precision, $roundingMode);
    }

    /**
     * @phpstan-param PHP_ROUND_HALF_UP|PHP_ROUND_HALF_DOWN|PHP_ROUND_HALF_EVEN|PHP_ROUND_HALF_ODD $roundingMode
     * @psalm-param positive-int $roundingMode
     * @return ($precision is positive-int ? float : int)
     */
    public function toDays(int $precision = 0, int $roundingMode = PHP_ROUND_HALF_UP): int|float
    {
        return $this->toX(self::MULT_DAYS, $precision, $roundingMode);
    }

    /**
     * @param self::MULT_* $multiplier
     * @phpstan-param PHP_ROUND_HALF_UP|PHP_ROUND_HALF_DOWN|PHP_ROUND_HALF_EVEN|PHP_ROUND_HALF_ODD $roundingMode
     * @psalm-param positive-int $roundingMode
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
            $formatedValue = match ($unit) {
                'd' => $value === 0 ? '' : (string) $value,
                'h', 'i', 's' => str_pad((string) $value, 2, '0', STR_PAD_LEFT),
                'ms', 'us', 'ns' => str_pad((string) $value, 3, '0', STR_PAD_LEFT),
            };
            $remaining %= self::UNITS_MULT_MAP[$unit];

            $result = str_replace($placeholder, $formatedValue, $result);
        }

        $sign = $this->nanoseconds < 0 ? '-' : '';

        return $sign . trim($result);
    }
}
