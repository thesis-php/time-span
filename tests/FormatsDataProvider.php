<?php

declare(strict_types=1);

namespace Thesis;

final class FormatsDataProvider
{
    private const int NANOSECONDS = 112_023_056_089_023;

    /**
     * @return \Generator<non-empty-string, array{non-empty-string, int, non-empty-string}>
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function formats(): \Generator
    {
        $formats = [
            '%d' => '1',
            '%h' => '31',
            '%i' => '1867',
            '%s' => '112023',
            '%ms' => '112023056',
            '%us' => '112023056089',
            '%ns' => '112023056089023',

            '%d %h:%i:%s.%ms_%us_%ns' => '1 07:07:03.056_089_023',
            '%d %h:%i:%s.%ms_%us' => '1 07:07:03.056_089',
            '%d %h:%i:%s.%ms' => '1 07:07:03.056',
            '%d %h:%i:%s' => '1 07:07:03',
            '%d %h:%i' => '1 07:07',
            '%d %h' => '1 07',
            '%h %d' => '07 1',

            '%ns_%us_%ms.%s:%i:%h %d' => '023_089_056.03:07:07 1',
            '%ns_%us_%ms.%s:%i:%h' => '023_089_056.03:07:31',
            '%ns_%us_%ms.%s:%i' => '023_089_056.03:1867',
            '%ns_%us_%ms.%s' => '023_089_056.112023',
            '%ns_%us_%ms' => '023_089_112023056',
            '%ns_%us' => '023_112023056089',

            '%h:%i:%s.%ms_%us_%ns' => '31:07:03.056_089_023',
            '%i:%s.%ms_%us_%ns' => '1867:03.056_089_023',
            '%s.%ms_%us_%ns' => '112023.056_089_023',
            '%ms_%us_%ns' => '112023056_089_023',
            '%us_%ns' => '112023056089_023',
        ];

        foreach ($formats as $format => $value) {
            yield $format => [$format, self::NANOSECONDS, $value];
            yield '-' . $format => [$format, -self::NANOSECONDS, '-' . $value];
        }

        yield 'trim_case %d %h:%i:%s.%ms_%us_%ns' => ['%d %h:%i:%s.%ms_%us_%ns', 82_023_056_089_023, '0 22:47:03.056_089_023'];
        yield 'trim_case -%d %h:%i:%s.%ms_%us_%ns' => ['%d %h:%i:%s.%ms_%us_%ns', -82_023_056_089_023, '-0 22:47:03.056_089_023'];

        yield 'zero_nanoseconds_case %d %h:%i:%s.%ms_%us_%ns' => ['%d %h:%i:%s.%ms_%us_%ns', 0, '0 00:00:00.000_000_000'];
    }
}
