# Changelog

All notable changes to `smony/filament-login-attempts` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-07-04

### Added
- Login attempt logging (`login_attempts` table) for successful and failed logins.
- Brute-force protection via `Illuminate\Cache\RateLimiter`, blocking login before credentials are checked once a threshold is crossed.
- Configurable lockout key strategy: `ip`, `email`, or `ip_and_email`.
- `LoginAttemptResource`: read-only list with device parsing, "Only failed" and "Currently locked out" filters, an "Unlocks in" column, and an "Unlock now" action.
- `FailedLoginsWidget` stat widget: failed attempts (24h) and currently locked out count.
- `Smony\FilamentLoginAttempts\Events\LoginLockedOut` event, dispatched the moment a key gets locked out.
- `login-attempts:prune` artisan command to delete attempts older than the configured retention period.
