<?php

declare(strict_types=1);

namespace Thesis\TimeSpan\Internal;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversFunction('Thesis\TimeSpan\Internal\isCastableToInt')]
final class IsCastableToIntTest extends TestCase
{
    #[DataProvider('provideCastableCases')]
    public function testCastable(float $value): void
    {
        self::assertTrue(isCastableToInt($value));
    }

    /**
     * @return \Generator<int, array{float}>
     */
    public static function provideCastableCases(): iterable
    {
        yield [0.0];
        yield [1.5];
        yield [-1_000];
        yield [(float) PHP_INT_MIN];
    }

    #[DataProvider('provideNotCastableCases')]
    public function testNotCastable(float $value): void
    {
        self::assertFalse(isCastableToInt($value));
    }

    /**
     * @return \Generator<int, array{float}>
     */
    public static function provideNotCastableCases(): iterable
    {
        yield [NAN];
        yield [INF];
        yield [-INF];
        // PHP_INT_MAX = 2^63 - 1, but float has 53-bit mantissa: the nearest representable value
        // rounds UP to 2^63, which exceeds int64 range
        yield [(float) PHP_INT_MAX];
        yield [PHP_INT_MAX * 2];
        yield [PHP_INT_MIN * 2];
    }
}
