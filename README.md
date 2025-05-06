# Thesis Time

[![PHP Version Requirement](https://img.shields.io/packagist/dependency-v/thesis/time/php)](https://packagist.org/packages/thesis/time)
[![GitHub Release](https://img.shields.io/github/v/release/thesis-php/time)](https://github.com/thesis-php/time/releases)
[![Code Coverage](https://codecov.io/gh/thesis-php/time/branch/0.1.x/graph/badge.svg)](https://codecov.io/gh/thesis-php/time/tree/0.1.x)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fthesis-php%2Ftime%2F0.1.x)](https://dashboard.stryker-mutator.io/reports/github.com/thesis-php/time/0.1.x)

## Installation

```shell
composer require thesis/time
```

## `Thesis\Time\WallClock`

A simple implementation of the [`Psr\Clock\ClockInterface`](https://www.php-fig.org/psr/psr-20/) that returns the current wall-clock time.

```php
use Thesis\Time\WallClock;

$clock = new WallClock();

echo $clock->now()->format('c'); // Outputs current time in ISO 8601 format
```

Or with a specific timezone:

```php
$clock = new WallClock(new \DateTimeZone('Europe/Moscow'));

echo $clock->now()->format('c'); // Outputs Moscow time in ISO 8601 format
```
