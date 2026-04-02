<?php

declare(strict_types=1);

namespace Thesis\Duration;

/**
 * @internal
 */
function toInt(int|float $value, ?\RoundingMode $roundingMode): int
{
    if (\is_int($value)) {
        return $value;
    }

    if (!(is_finite($value) && $value >= PHP_INT_MIN && $value < PHP_INT_MAX)) {
        throw new \OverflowException();
    }

    if (fmod($value, 1.0) !== 0.0) {
        if ($roundingMode === null) {
            throw new \LogicException('Rounding mode required');
        }

        $value = round($value, mode: $roundingMode);
    }

    return (int) $value;
}
