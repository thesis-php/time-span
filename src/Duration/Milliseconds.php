<?php

declare(strict_types=1);

namespace Thesis\Duration;

class Milliseconds extends Microseconds
{
    public function __construct(
        int|float $milliseconds = 0,
        ?\RoundingMode $roundingMode = null,
    ) {
        parent::__construct(
            microseconds: $milliseconds * 1_000,
            roundingMode: $roundingMode,
        );
    }
}
