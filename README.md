# TimeSpan

`TimeSpan` is an immutable value object representing a time interval with **nanosecond precision**. It provides a robust way to handle time durations, perform arithmetic operations, and format time strings without the common pitfalls of floating-point math or mutable state.

## Installation

```bash
composer require your-vendor/time-span
```

## Usage

You can create a `TimeSpan` instance using various static factory methods depending on your source unit.

```php
use YourVendor\TimeSpan;

// From specific units
$span = TimeSpan::fromSeconds(90);
$span = TimeSpan::fromMinutes(5.5);
$span = TimeSpan::fromHours(1);
$span = TimeSpan::fromDays(2);

// From a combination of units
$span = TimeSpan::from(
    days: 1,
    hours: 12,
    minutes: 30
);

// From a DateInterval
$span = TimeSpan::fromInterval(new \DateInterval('P1DT12H'));
```

### ⚠️ Important: Nanoseconds and IEEE 754

The core method `TimeSpan::fromNanoseconds(int|float $nanoseconds)` accepts both integers and floating-point numbers. However, users must be aware of **IEEE 754 floating-point limitations**.

When passing large nanosecond values as `float`, you may encounter precision loss. This is not a bug in the library but a fundamental property of how computers handle floating-point numbers.

*   **Safe:** Integers (up to `PHP_INT_MAX`) are always precise.
*   **Risky:** Large floats (e.g., converting huge values from other units) may be rounded to the nearest representable number.

If the provided float value exceeds the safe integer range for nanoseconds, the library will throw an `OutOfBoundsException` to prevent silent overflow errors.

```php
// Safe (Integer)
$span = TimeSpan::fromNanoseconds(1000);

// Caution (Float): Very large floats may lose 1-2 nanoseconds of precision
$span = TimeSpan::fromNanoseconds(1000.5); // Rounded to nearest integer
```

## Formatting

The `format()` method allows you to generate human-readable strings from the time interval.

This method is **context-aware**: it automatically calculates values based on the largest unit you request. For example, if you only ask for minutes (`%i`), it will show the *total* minutes. If you ask for hours and minutes (`%h:%i`), it will show total hours and the remaining minutes.

```php
public function format(string $format = '%h:%i:%s'): string
```

### Available Placeholders

| Placeholder | Description | Example Output |
| :--- | :--- | :--- |
| `%d` | Days | `1`, `106751` |
| `%h` | Hours (00-23 usually, or total if largest) | `05`, `150` |
| `%i` | Minutes (00-59 usually, or total if largest) | `09`, `90` |
| `%s` | Seconds (00-59 usually, or total if largest) | `30`, `120` |
| `%ms` | Milliseconds (000-999) | `050` |
| `%us` | Microseconds (000-999) | `001` |
| `%ns` | Nanoseconds (000-999) | `999` |

### Formatting Examples

Assuming a time span of **1 day, 7 hours, 7 minutes, 3 seconds, 56ms, 89us, 23ns**:

```php
// Full detail
echo $span->format('%d %h:%i:%s.%ms_%us_%ns');
// Output: "1 07:07:03.056_089_023"
// or
echo $span->format('%d %h:%i:%s.%ns');
// Output: "1 07:07:03.056089023"

// Total hours (Days are converted to hours)
echo $span->format('%h:%i:%s');
// Output: "31:07:03" (1 day + 7 hours = 31 hours)

// Total seconds
echo $span->format('%s');
// Output: "112023" (Total seconds in the span)

// Just minutes and seconds
echo $span->format('%i:%s');
// Output: "1867:03"
```

## Math Operations

The library includes a comprehensive set of immutable mathematical operations. Since `TimeSpan` is a value object, these methods always return a **new instance**.

*   **Arithmetic:** `add()`, `sub()`, `mul()`, `div()`
*   **Helpers:** `abs()`, `negated()`

```php
$span1 = TimeSpan::fromMinutes(10);
$span2 = TimeSpan::fromMinutes(5);

$total = $span1->add($span2); // 15 minutes
$diff  = $span1->sub($span2); // 5 minutes
$double = $span1->mul(2);     // 20 minutes
```

## Comparison

You can compare `TimeSpan` objects directly without converting them manually:

*   `compareTo()`
*   `isEqualTo()`
*   `isGreaterThan()` / `isGreaterThanOrEqualTo()`
*   `isLessThan()` / `isLessThanOrEqualTo()`
*   `isZero()`, `isPositive()`, `isNegative()`

```php
if ($span1->isGreaterThan($span2)) {
    // ...
}
```
