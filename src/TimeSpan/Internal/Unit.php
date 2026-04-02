<?php

declare(strict_types=1);

namespace Thesis\TimeSpan\Internal;

/**
 * @internal
 */
final readonly class Unit
{
    public const int NANOSECONDS  = 1;
    public const int MICROSECONDS = self::NANOSECONDS  * 1_000;
    public const int MILLISECONDS = self::MICROSECONDS * 1_000;
    public const int SECONDS      = self::MILLISECONDS * 1_000;
    public const int MINUTES      = self::SECONDS      * 60;
    public const int HOURS        = self::MINUTES      * 60;
    public const int DAYS         = self::HOURS        * 24;

    private function __construct() {}
}
