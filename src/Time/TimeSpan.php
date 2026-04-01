<?php

declare(strict_types=1);

namespace Thesis\Time;

class_alias(\Thesis\TimeSpan::class, TimeSpan::class);

/** @phpstan-ignore if.alwaysFalse */
if (false) {
    /**
     * @deprecated since 0.2.5, use {@see \Thesis\TimeSpan} instead
     * @phpstan-ignore class.extendsFinalByPhpDoc
     */
    final readonly class TimeSpan extends \Thesis\TimeSpan {}
}
