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
        return new self((int) $a->format('Uu') * 1000 - (int) $b->format('Uu') * 1000);
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
}
