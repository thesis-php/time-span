<?php

declare(strict_types=1);

namespace Thesis\TimeSpan\Internal;

/**
 * @internal
 *
 * @return non-empty-array<string, string>
 */
function calculateFormatReplacements(string $format, int $nanoseconds): array
{
    /**
     * Ordered largest-to-smallest for correct remainder calculation.
     *
     * @var non-empty-array<non-empty-string, array{positive-int, non-negative-int}>
     */
    static $placeholders = [
        '%d' => [Unit::DAYS, 0],
        '%h' => [Unit::HOURS, 2],
        '%i' => [Unit::MINUTES, 2],
        '%s' => [Unit::SECONDS, 2],
        '%ms' => [Unit::MILLISECONDS, 3],
        '%us' => [Unit::MICROSECONDS, 3],
        '%ns' => [Unit::NANOSECONDS, 3],
    ];
    /** @var ?non-empty-string */
    static $pattern = null;
    $pattern ??= \sprintf('/%s/', implode('|', array_keys($placeholders)));

    preg_match_all($pattern, $format, $matches);
    $present = array_flip($matches[0] ?? []);

    // abs(PHP_INT_MIN) overflows to float, so we use PHP_INT_MAX and compensate below
    $isIntMin = $nanoseconds === PHP_INT_MIN;
    $remaining = $isIntMin ? PHP_INT_MAX : abs($nanoseconds);

    $replacements = [
        '%-' => $nanoseconds < 0 ? '-' : '',
    ];

    foreach ($placeholders as $placeholder => [$multiplier, $width]) {
        if (!isset($present[$placeholder])) {
            continue;
        }

        $value = intdiv($remaining, $multiplier);
        $remaining -= $value * $multiplier;

        // PHP_INT_MIN = -(PHP_INT_MAX + 1), so we computed replacements for PHP_INT_MAX
        // which is 1 nanosecond short. The missing nanosecond always lands in %ns because
        // PHP_INT_MAX % Unit::NANOSECONDS = 807, so +1 = 808 — no carry into higher units.
        if ($isIntMin && $placeholder === '%ns') {
            ++$value;
        }

        $replacements[$placeholder] = $width > 0
            ? str_pad((string) $value, $width, '0', STR_PAD_LEFT)
            : (string) $value;
    }

    return $replacements;
}
