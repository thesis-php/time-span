<?php

declare(strict_types=1);

namespace Thesis\TimeSpan\Internal;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversFunction('Thesis\TimeSpan\Internal\calculateFormatReplacements')]
final class CalculateFormatReplacementsTest extends TestCase
{
    /**
     * @param array<string, string> $expected
     */
    #[DataProvider('provideCases')]
    public function test(string $format, int $nanoseconds, array $expected): void
    {
        $replacements = calculateFormatReplacements($format, $nanoseconds);

        self::assertSame($expected, $replacements);
    }

    /**
     * @return \Generator<string, array{string, int, array<string, string>}>
     */
    public static function provideCases(): iterable
    {
        $fullFormat = '%d %h:%i:%s.%ms_%us_%ns';

        yield 'zero' => [$fullFormat, 0, [
            '%-' => '',
            '%d' => '0', '%h' => '00', '%i' => '00', '%s' => '00',
            '%ms' => '000', '%us' => '000', '%ns' => '000',
        ]];

        yield 'positive' => [$fullFormat, 112_023_056_089_023, [
            '%-' => '',
            '%d' => '1', '%h' => '07', '%i' => '07', '%s' => '03',
            '%ms' => '056', '%us' => '089', '%ns' => '023',
        ]];

        yield 'negative' => [$fullFormat, -112_023_056_089_023, [
            '%-' => '-',
            '%d' => '1', '%h' => '07', '%i' => '07', '%s' => '03',
            '%ms' => '056', '%us' => '089', '%ns' => '023',
        ]];

        yield 'sub-day' => [$fullFormat, 3_723_004_005_006, [
            '%-' => '',
            '%d' => '0', '%h' => '01', '%i' => '02', '%s' => '03',
            '%ms' => '004', '%us' => '005', '%ns' => '006',
        ]];

        yield 'exactly one day' => [$fullFormat, 86_400_000_000_000, [
            '%-' => '',
            '%d' => '1', '%h' => '00', '%i' => '00', '%s' => '00',
            '%ms' => '000', '%us' => '000', '%ns' => '000',
        ]];

        yield 'PHP_INT_MAX' => [$fullFormat, PHP_INT_MAX, [
            '%-' => '',
            '%d' => '106751', '%h' => '23', '%i' => '47', '%s' => '16',
            '%ms' => '854', '%us' => '775', '%ns' => '807',
        ]];

        yield 'PHP_INT_MIN' => [$fullFormat, PHP_INT_MIN, [
            '%-' => '-',
            '%d' => '106751', '%h' => '23', '%i' => '47', '%s' => '16',
            '%ms' => '854', '%us' => '775', '%ns' => '808',
        ]];

        // Cumulative: first present unit accumulates all larger units
        yield '%h only' => ['%h', 112_023_056_089_023, [
            '%-' => '',
            '%h' => '31',
        ]];

        yield '%i only' => ['%i', 112_023_056_089_023, [
            '%-' => '',
            '%i' => '1867',
        ]];

        yield '%s only' => ['%s', 112_023_056_089_023, [
            '%-' => '',
            '%s' => '112023',
        ]];

        // Absent intermediate unit: its value is absorbed into the next smaller present unit
        // 112_023_056_089_023 ns = 1 day + 25_623_056_089_023 ns remaining
        // without %h: %i = intdiv(25_623_056_089_023, 60_000_000_000) = 427
        yield '%d and %i without %h' => ['%d %i', 112_023_056_089_023, [
            '%-' => '',
            '%d' => '1', '%i' => '427',
        ]];
    }
}
