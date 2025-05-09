# Thesis TimeSpan

[![PHP Version Requirement](https://img.shields.io/packagist/dependency-v/thesis/time-span/php)](https://packagist.org/packages/thesis/time-span)
[![GitHub Release](https://img.shields.io/github/v/release/thesis-php/time-span)](https://github.com/thesis-php/time-span/releases)
[![Code Coverage](https://codecov.io/gh/thesis-php/time-span/branch/0.1.x/graph/badge.svg)](https://codecov.io/gh/thesis-php/time-span/tree/0.1.x)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fthesis-php%2Ftime-span%2F0.1.x)](https://dashboard.stryker-mutator.io/reports/github.com/thesis-php/time-span/0.1.x)

## Installation

```shell
composer require thesis/time-span
```

## Usage

```php
use Thesis\Time\TimeSpan;

$delay = TimeSpan::fromSeconds(25.123);

echo $delay->toMilliseconds(); // 25123
```
