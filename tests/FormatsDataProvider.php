<?php

declare(strict_types=1);

namespace Thesis;

final readonly class FormatsDataProvider
{
    private const int NANOSECONDS = 112_023_056_089_023;

    /**
     * @return \Generator<non-empty-string, array{non-empty-string, int, non-empty-string}>
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function formats(): \Generator
    {
        $ns = self::NANOSECONDS;

        // Single unit placeholders (cumulative — first present unit accumulates all larger units)
        yield '%d' => ['%d', $ns, '1'];
        yield '%h' => ['%h', $ns, '31'];
        yield '%i' => ['%i', $ns, '1867'];
        yield '%s' => ['%s', $ns, '112023'];
        yield '%ms' => ['%ms', $ns, '112023056'];
        yield '%us' => ['%us', $ns, '112023056089'];
        yield '%ns' => ['%ns', $ns, '112023056089023'];

        // Multi-unit, largest to smallest
        yield '%d %h:%i:%s.%ms_%us_%ns' => ['%d %h:%i:%s.%ms_%us_%ns', $ns, '1 07:07:03.056_089_023'];
        yield '%d %h:%i:%s.%ms_%us' => ['%d %h:%i:%s.%ms_%us', $ns, '1 07:07:03.056_089'];
        yield '%d %h:%i:%s.%ms' => ['%d %h:%i:%s.%ms', $ns, '1 07:07:03.056'];
        yield '%d %h:%i:%s' => ['%d %h:%i:%s', $ns, '1 07:07:03'];
        yield '%d %h:%i' => ['%d %h:%i', $ns, '1 07:07'];
        yield '%d %h' => ['%d %h', $ns, '1 07'];
        yield '%h %d' => ['%h %d', $ns, '07 1'];

        // Multi-unit, smallest to largest (reversed order in format string)
        yield '%ns_%us_%ms.%s:%i:%h %d' => ['%ns_%us_%ms.%s:%i:%h %d', $ns, '023_089_056.03:07:07 1'];
        yield '%ns_%us_%ms.%s:%i:%h' => ['%ns_%us_%ms.%s:%i:%h', $ns, '023_089_056.03:07:31'];
        yield '%ns_%us_%ms.%s:%i' => ['%ns_%us_%ms.%s:%i', $ns, '023_089_056.03:1867'];
        yield '%ns_%us_%ms.%s' => ['%ns_%us_%ms.%s', $ns, '023_089_056.112023'];
        yield '%ns_%us_%ms' => ['%ns_%us_%ms', $ns, '023_089_112023056'];
        yield '%ns_%us' => ['%ns_%us', $ns, '023_112023056089'];

        // Without %d (cumulative from %h)
        yield '%h:%i:%s.%ms_%us_%ns' => ['%h:%i:%s.%ms_%us_%ns', $ns, '31:07:03.056_089_023'];
        yield '%i:%s.%ms_%us_%ns' => ['%i:%s.%ms_%us_%ns', $ns, '1867:03.056_089_023'];
        yield '%s.%ms_%us_%ns' => ['%s.%ms_%us_%ns', $ns, '112023.056_089_023'];
        yield '%ms_%us_%ns' => ['%ms_%us_%ns', $ns, '112023056_089_023'];
        yield '%us_%ns' => ['%us_%ns', $ns, '112023056089_023'];

        // Negative spans (sign requires %- in format)
        yield '%-%d %h:%i:%s.%ms_%us_%ns negative' => ['%-%d %h:%i:%s.%ms_%us_%ns', -$ns, '-1 07:07:03.056_089_023'];
        yield '%-%h:%i:%s negative' => ['%-%h:%i:%s', -$ns, '-31:07:03'];
        yield '%-%h:%i:%s.%ms_%us_%ns negative' => ['%-%h:%i:%s.%ms_%us_%ns', -$ns, '-31:07:03.056_089_023'];

        // d=0 (sub-day spans)
        yield 'sub-day %d %h:%i:%s.%ms_%us_%ns' => ['%d %h:%i:%s.%ms_%us_%ns', 82_023_056_089_023, '0 22:47:03.056_089_023'];
        yield 'sub-day %-%d %h:%i:%s.%ms_%us_%ns negative' => ['%-%d %h:%i:%s.%ms_%us_%ns', -82_023_056_089_023, '-0 22:47:03.056_089_023'];

        // Cumulative unit is determined by size, not position in format string
        yield 'reversed %i:%h' => ['%i:%h', $ns, '07:31'];

        // Repeated placeholder
        yield 'repeated %h' => ['%h h %h:%i:%s', $ns, '31 h 31:07:03'];

        // No placeholders — literal string passthrough
        yield 'no placeholders' => ['fixed 5 seconds', $ns, 'fixed 5 seconds'];

        // Zero
        yield 'zero' => ['%d %h:%i:%s.%ms_%us_%ns', 0, '0 00:00:00.000_000_000'];
    }

    private function __construct() {}
}
