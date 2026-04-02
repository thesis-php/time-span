<?php

declare(strict_types=1);

namespace Thesis\Duration;

class Microseconds extends Nanoseconds
{
    public function __construct(
        int|float $microseconds = 0,
        ?\RoundingMode $roundingMode = null,
    ) {
        parent::__construct(
            nanoseconds: $microseconds * 1_000,
            roundingMode: $roundingMode,
        );
    }
}
