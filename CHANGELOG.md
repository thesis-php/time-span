# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.3] - 2025-10-06

### Changed

- Make `TimeSpan::__construct()` public (#20).

## [0.2.2] - 2025-05-30

### Added

- `TimeSpan::fromInterval()` (#5).
- Math methods: `TimeSpan::abs()`, `TimeSpan::negated()`, `TimeSpan::compareTo()`, `TimeSpan::isEqualTo()`,
  `TimeSpan::isLessThan()`, `TimeSpan::isLessThanOrEqualTo()`, `TimeSpan::isZero()`,
  `TimeSpan::isGreaterThanOrEqualTo()`, `TimeSpan::isGreaterThan()`, `TimeSpan::isNegative()`,
  `TimeSpan::isNegativeOrZero()`, `TimeSpan::isPositive()`, `TimeSpan::isPositiveOrZero()`, `TimeSpan::add()`,
  `TimeSpan::sub()`, `TimeSpan::mul()`, `TimeSpan::div()` (#14).
- `TimeSpan::format()` (#17).

## [0.2.1] - 2025-05-13

### Changed

- Store in nanoseconds (#7).
