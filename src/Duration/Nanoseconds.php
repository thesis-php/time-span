<?php

declare(strict_types=1);

namespace Thesis\Duration;

use Thesis\TimeSpan\Internal\Unit;

/**
 * @phpstan-consistent-constructor
 */
class Nanoseconds
{
    private int $nanoseconds;

    public function __construct(int|float $nanoseconds = 0, ?\RoundingMode $roundingMode = null)
    {
        $this->nanoseconds = toInt($nanoseconds, $roundingMode);
    }

    final public function abs(): static
    {
        if ($this->nanoseconds >= 0) {
            return $this;
        }

        return new static(-$this->nanoseconds);
    }

    final public function negated(): static
    {
        return new static(-$this->nanoseconds);
    }

    final public function toMicroseconds(?\RoundingMode $roundingMode = null): Microseconds
    {
        if ($this instanceof Microseconds) {
            return $this;
        }

        return new Microseconds($this->nanoseconds / Unit::MICROSECONDS, $roundingMode);
    }

    final public function toMilliseconds(?\RoundingMode $roundingMode = null): Milliseconds
    {
        if ($this instanceof Milliseconds) {
            return $this;
        }

        return new Milliseconds($this->nanoseconds / Unit::MILLISECONDS, $roundingMode);
    }

    final public function totalNanoseconds(): int
    {
        return $this->nanoseconds;
    }

    final public function totalMicroseconds(\RoundingMode $roundingMode = \RoundingMode::HalfAwayFromZero): int
    {
        return (int) round($this->nanoseconds / Unit::MICROSECONDS, mode: $roundingMode);
    }

    final public function totalMilliseconds(\RoundingMode $roundingMode = \RoundingMode::HalfAwayFromZero): int
    {
        return (int) round($this->nanoseconds / Unit::MILLISECONDS, mode: $roundingMode);
    }

    /**
     * @template T of self
     * @param T $duration
     * @return ($duration is static ? static : T)
     */
    final public function add(self $duration): self
    {
        $sum = $this->nanoseconds + $duration->nanoseconds;

        if ($duration instanceof $this) {
            return $this->with($sum);
        }

        return $duration->with($sum);
    }

    /**
     * @template T of self
     * @param T $duration
     * @return ($duration is static ? static : T)
     */
    final public function sub(self $duration): self
    {
        $diff = $this->nanoseconds - $duration->nanoseconds;

        if ($this instanceof $duration) {
            return $duration->with($diff);
        }

        return $this->with($diff);
    }

    /**
     * @return ($times is int ? static : self)
     */
    final public function mul(int|float $times): self
    {
        $product = $this->nanoseconds * $times;

        if (\is_int($times)) {
            return $this->with($product);
        }

        return new self($this->nanoseconds * $times);
    }

    final public function div(int|float $factor): self
    {
        return new self($this->nanoseconds / $factor);
    }

    private function with(int|float $nanoseconds): static
    {
        $copy = clone $this;
        $copy->nanoseconds = toInt($nanoseconds, \RoundingMode::HalfAwayFromZero);

        return $copy;
    }
}
