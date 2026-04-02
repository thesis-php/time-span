# Thesis TimeSpan

[![PHP Version Requirement](https://img.shields.io/packagist/dependency-v/thesis/time-span/php)](https://packagist.org/packages/thesis/time-span)
[![GitHub Release](https://img.shields.io/github/v/release/thesis-php/time-span)](https://github.com/thesis-php/time-span/releases)
[![Code Coverage](https://codecov.io/gh/thesis-php/time-span/branch/0.2.x/graph/badge.svg)](https://codecov.io/gh/thesis-php/time-span/tree/0.2.x)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fthesis-php%2Ftime-span%2F0.2.x)](https://dashboard.stryker-mutator.io/reports/github.com/thesis-php/time-span/0.2.x)

An immutable, nanosecond-precise time duration type for PHP 8.3+.

```php
$span = TimeSpan::from(hours: 1, minutes: 30);

echo $span->toMinutes(); // 90
echo $span->format();    // 01:30:00
```

## Installation

```shell
composer require thesis/time-span
```

## Why not DateInterval?

PHP's built-in `DateInterval` covers calendar durations — periods like "3 months" or "1 year" that only make sense
relative to a specific date. `TimeSpan` covers the complementary case: a **fixed amount of elapsed time** that exists
independently of any calendar.

|                       | `DateInterval`        | `TimeSpan`                 |
|-----------------------|-----------------------|----------------------------|
| Represents            | calendar period       | fixed duration             |
| Supports months/years | yes                   | no                         |
| Immutable             | no                    | yes                        |
| Arithmetic            | no                    | `add` `sub` `mul` `div`    |
| Comparison            | no                    | `compareTo` `isLessThan` … |
| Negative values       | awkward `invert` flag | signed int                 |
| Precision             | microseconds          | nanoseconds                |

`DateInterval` is the right tool when you need "30 days from now" or "next month". `TimeSpan` is the right tool when you
need "wait 30 seconds" or "this request took 42 ms".

## Use cases

```php
// HTTP / DB timeouts
$client = new HttpClient(timeout: TimeSpan::fromSeconds(30));

// Cache TTL
$cache->set($key, $value, ttl: TimeSpan::fromMinutes(5));

// Rate limiting — window duration
$limiter = new RateLimiter(window: TimeSpan::fromHours(1), limit: 1000);

// Retry with exponential backoff
$delay = TimeSpan::fromMilliseconds(100);
foreach (range(1, 5) as $attempt) {
    try {
        return $this->call();
    } catch (TransientException) {
        sleep($delay->toSeconds());
        $delay = $delay->mul(2);
    }
}

// Benchmarking
$start = TimeSpan::hrtime();
$result = $this->heavyComputation();
$elapsed = TimeSpan::hrtime()->sub($start);
$this->logger->info("Computed in {$elapsed->toMilliseconds(precision: 2)} ms");

// SLA / deadline check
$elapsed = TimeSpan::between($requestTime, new \DateTimeImmutable());
if ($elapsed->isGreaterThan(TimeSpan::fromSeconds(5))) {
    $this->metrics->increment('sla.violated');
}
```

## Creating a TimeSpan

### From multiple units

```php
use Thesis\TimeSpan;

$span = TimeSpan::from(days: 1, hours: 2, minutes: 30, seconds: 15);
$span = TimeSpan::from(milliseconds: 250);
$span = TimeSpan::from(seconds: 90); // same as 1 minute 30 seconds
```

### From a single unit

```php
$span = TimeSpan::fromNanoseconds(1_500_000);
$span = TimeSpan::fromMicroseconds(1_500);
$span = TimeSpan::fromMilliseconds(1.5);
$span = TimeSpan::fromSeconds(90);
$span = TimeSpan::fromMinutes(1.5);
$span = TimeSpan::fromHours(0.25);
$span = TimeSpan::fromDays(7);
```

All constructors accept `int|float`. Floats are rounded to the nearest nanosecond.

### From a DateInterval

```php
TimeSpan::fromInterval(new \DateInterval('PT90S')); // 90 seconds
TimeSpan::fromInterval(new \DateInterval('P7D'));   // 7 days
```

> **Note:** Intervals with years or months cannot be converted to a fixed duration and will throw an
`InvalidArgumentException`. Intervals produced by `DateTimeInterface::diff()` are also rejected due to DST ambiguity —
> use `TimeSpan::between()` instead.

### Between two datetimes

```php
$start = new \DateTimeImmutable('2024-01-01 10:00:00');
$end   = new \DateTimeImmutable('2024-01-01 11:30:00');

$span = TimeSpan::between($start, $end); // 1 hour 30 minutes
```

The result is signed: `between($a, $b)` returns a negative span if `$b` is in the past relative to `$a`.

### From the high-resolution timer

```php
$start = TimeSpan::hrtime();
doSomething();
$elapsed = TimeSpan::hrtime()->sub($start);

echo $elapsed->toMilliseconds(precision: 3); // e.g. 42.731
```

### Directly from nanoseconds

```php
new TimeSpan(5_000_000_000); // 5 seconds
new TimeSpan();              // zero span
```

## Converting to other units

Every `to*()` method returns `int` by default. Pass a `$precision` argument to get a `float` with that many decimal
places.

```php
$span = TimeSpan::from(
    days: 1,
    hours: 1,
    minutes: 30,
    seconds: 45,
    milliseconds: 500,
    microseconds: 89,
    nanoseconds: 23,
);

$span->toNanoseconds();         // 91_845_500_089_023
$span->toMicroseconds();        // 91_845_500_089
$span->toMilliseconds();        // 91_845_500
$span->toSeconds();             // 91846
$span->toSeconds(precision: 1); // 91845.5
$span->toMinutes();             // 1531
$span->toMinutes(precision: 4); // 1530.7583
$span->toHours();               // 26
$span->toDays();                // 1
```

The optional second argument controls rounding mode (defaults to `PHP_ROUND_HALF_UP`):

```php
$span->toSeconds(precision: 2, roundingMode: PHP_ROUND_HALF_DOWN);
$span->toSeconds(precision: 3, roundingMode: PHP_ROUND_HALF_EVEN);
```

## Arithmetic

All arithmetic methods return a new `TimeSpan` instance and throw `\OverflowException`
if the result exceeds the `int` range (~292 years in nanoseconds on 64-bit platform).

```php
$m30 = TimeSpan::fromMinutes(30);
$m15 = TimeSpan::fromMinutes(15);

$m30->add($m15);  // 45 minutes
$m30->sub($m15);  // 15 minutes
$m30->mul(3);   // 90 minutes
$m30->mul(0.5); // 15 minutes
$m30->div(2);   // 15 minutes
$m30->div(3);   // 10 minutes
```

`div()` throws `\DivisionByZeroError` when the factor is `0`.

## Comparison

```php
$s10 = TimeSpan::fromSeconds(10);
$s20 = TimeSpan::fromSeconds(20);

$s10->compareTo($s20);              // -1 (less than)
$s20->compareTo($s10);              // 1  (greater than)
$s10->compareTo($s10);              // 0  (equal)

$s10->isEqualTo($s20);              // false
$s10->isLessThan($s20);             // true
$s10->isLessThanOrEqualTo($s20);    // true
$s10->isGreaterThan($s20);          // false
$s10->isGreaterThanOrEqualTo($s20); // false
```

## Sign checks

```php
$s_5 = TimeSpan::fromSeconds(-5);

$s_5->isNegative();       // true
$s_5->isNegativeOrZero(); // true
$s_5->isPositive();       // false
$s_5->isPositiveOrZero(); // false
$s_5->isZero();           // false

$s_5->abs();              // TimeSpan(5 seconds)
$s_5->negated();          // TimeSpan(5 seconds)

TimeSpan::fromSeconds(5)->negated(); // TimeSpan(-5 seconds)
```

## Formatting

### format()

`format()` renders a span as a human-readable string. The default pattern is `%-%h:%i:%s`.

| Placeholder | Unit                  | Width             |
|-------------|-----------------------|-------------------|
| `%-`        | sign                  | 0–1               |
| `%d`        | days                  | >=1 (unpadded)    |
| `%h`        | hours                 | >=2 (zero-padded) |
| `%i`        | minutes               | >=2 (zero-padded) |
| `%s`        | seconds               | >=2 (zero-padded) |
| `%ms`       | milliseconds          | >=3 (zero-padded) |
| `%us`       | microseconds          | >=3 (zero-padded) |
| `%ns`       | nanoseconds           | >=3 (zero-padded) |

The **largest unit present** in the format receives the total cumulative value; each smaller unit shows
only the remainder after the larger ones are subtracted:

```php
$span = TimeSpan::from(
    days: 1,
    hours: 2,
    minutes: 3,
    seconds: 4,
    milliseconds: 500,
    microseconds: 600,
    nanoseconds: 700,
);

$span->format('%-%d %h:%i:%s.%ms_%us_%ns'); // "1 02:03:04.500_600_700"
$span->format('%-%d %h:%i:%s');             // "1 02:03:04"
$span->format('%-%d');                      // "1"
$span->format();                            // "26:03:04"
$span->format('%-%h:%i:%s.%ms');            // "26:03:04.500"
$span->format('%-%h:%i:%s.%ms_%us_%ns');    // "26:03:04.500_600_700"
$span->format('%-%i:%s.%ms_%us_%ns');       // "1563:04.500_600_700"
$span->format('%-%ns');                     // "93784500600700"
$span->format('%-%h h %h:%i:%s');           // "26 h 26:03:04" (repeated placeholders are fine)
$span->format('fixed 5 seconds');           // "fixed 5 seconds" (no placeholders — literal string)
```

The sign is only included when `%-` is explicitly present in the format:

```php
$span = TimeSpan::fromSeconds(-90);

$span->format('%-%i:%s'); // "-01:30"
$span->format('%i:%s');   // "01:30" — no sign without %-
```

### __toString()

`__toString()` is equivalent to `format()` with the default pattern:

```php
$span = TimeSpan::from(hours: 1, minutes: 30);

echo $span; // "01:30:00"
```

## License

MIT
