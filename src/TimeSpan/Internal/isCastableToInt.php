<?php

declare(strict_types=1);

namespace Thesis\TimeSpan\Internal;

/**
 * @internal
 */
function isCastableToInt(float $float): bool
{
    return is_finite($float) && $float >= PHP_INT_MIN && $float < PHP_INT_MAX;
}
