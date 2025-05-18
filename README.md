# Thesis TimeSpan

[![PHP Version Requirement](https://img.shields.io/packagist/dependency-v/thesis/time-span/php)](https://packagist.org/packages/thesis/time-span)
[![GitHub Release](https://img.shields.io/github/v/release/thesis-php/time-span)](https://github.com/thesis-php/time-span/releases)
[![Code Coverage](https://codecov.io/gh/thesis-php/time-span/branch/0.2.x/graph/badge.svg)](https://codecov.io/gh/thesis-php/time-span/tree/0.2.x)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fthesis-php%2Ftime-span%2F0.2.x)](https://dashboard.stryker-mutator.io/reports/github.com/thesis-php/time-span/0.2.x)

# TimeSpan - PHP Time Duration Library

A PHP library for representing and manipulating time durations with nanosecond precision. It provides an immutable `TimeSpan` object, making it safe and predictable to work with time differences.

This library is particularly useful when you need to:
*   Calculate precise differences between two `DateTimeImmutable` objects.
*   Represent fixed durations (e.g., timeouts, intervals) with high accuracy.
*   Convert durations between various units (days, hours, minutes, seconds, milliseconds, microseconds, nanoseconds).

## Features

*   **Nanosecond Precision:** Internally stores all durations in nanoseconds.
*   **Immutable:** `TimeSpan` objects are immutable, ensuring that once a duration is created, it cannot be accidentally changed.
*   **Flexible Creation:**
    *   From individual time components (days, hours, minutes, etc.).
    *   From a total number of nanoseconds, microseconds, milliseconds, seconds, minutes, hours, or days.
    *   By calculating the difference between two `DateTimeImmutable` objects.
    *   From a `DateInterval` object (with some limitations).
*   **Easy Conversion:** Convert `TimeSpan` objects to various time units, with options for precision and rounding.
*   **Handles `DateInterval` Caveats:** Provides specific warnings and behavior when converting from `DateInterval` to avoid inaccuracies related to months, years, and DST transitions.


## Installation

```shell
composer require thesis/time-span
```

## Usage

### Creating TimeSpans

```php
use Thesis\Time\TimeSpan;

// From specific units
$span1 = TimeSpan::fromNanoseconds(1_000_000);
$span2 = TimeSpan::fromMicroseconds(1000);
$span3 = TimeSpan::fromMilliseconds(1);
$span4 = TimeSpan::fromSeconds(60);
$span5 = TimeSpan::fromMinutes(1);
$span6 = TimeSpan::fromHours(1);
$span7 = TimeSpan::fromDays(1);

// From multiple units
$span8 = TimeSpan::from(
    days: 1,
    hours: 2,
    minutes: 30,
    seconds: 15,
    milliseconds: 500,
    microseconds: 250,
    nanoseconds: 100
);

// From DateTime difference
$start = new \DateTimeImmutable('2023-01-01 00:00:00');
$end = new \DateTimeImmutable('2023-01-02 01:30:15.500250100');
$span9 = TimeSpan::diff($end, $start);

// From DateInterval (with limitations)
$interval = new \DateInterval('P1DT2H30M15S');
$span10 = TimeSpan::fromInterval($interval);
```

### Converting TimeSpans

```php
$span = TimeSpan::fromHours(1.5);

// Exact conversions
$nanoseconds = $span->toNanoseconds(); // int(5400000000000)

// Convert with precision control
$seconds1 = $span->toSeconds();       // int(5400)
$seconds2 = $span->toSeconds(2);      // float(5400.00)
$millis1 = $span->toMilliseconds();   // int(5400000)
$millis2 = $span->toMilliseconds(1);  // float(5400000.0)

// With different rounding modes
$minutes = $span->toMinutes(1, PHP_ROUND_HALF_UP);   // float(90.0)
$hours = $span->toHours(3, PHP_ROUND_HALF_DOWN);     // float(1.500)
```

## Limitations

### DateInterval Conversion

* ❌ **Does not support months or years**  
  Throws `InvalidArgumentException` if the interval contains month/year values
* ❌ **Cannot handle intervals from `DateTime::diff()`**  
  Due to DST changeovers, these intervals can't be interpreted correctly
* ✅ **Recommended alternative**:
  ```php
  // Instead of:
  $interval = $date1->diff($date2);
  TimeSpan::fromInterval($interval);
  
  // Use:
  TimeSpan::diff($date1, $date2);
  ```

## Best Practices

### When Creating TimeSpans

1. For date/time differences:
   ```php
   // 👍 Preferred
   TimeSpan::diff($end, $start);
    
   // 👎 Avoid (unless you handle limitations)
   TimeSpan::fromInterval($start->diff($end));
   ```
2. For high precision:
   ```php
   // Work with smaller units when precision matters
   $span = TimeSpan::fromMicroseconds(1500.75);
   ```

### When Converting Units

1. Be explicit about rounding:
   ```php
    // 👍 Good - explicit about precision and rounding
    $hours = $span->toHours(2, PHP_ROUND_HALF_UP);

    // 👎 Avoid - implicit integer conversion
    $hours = $span->toHours(); // Loses fractional part
   ```
2. Choose appropriate units:
   ```php
   // For precise calculations:
   $nanos = $span->toNanoseconds();

   // For human-readable output:
   $readable = $span->toHours(2);
   ```