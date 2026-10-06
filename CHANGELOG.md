# Changelog

All notable changes to `laravel-stash` will be documented in this file

## 1.0.0 - 2026-10-06

Ground-up rewrite around pluggable drivers.

### Added

- Driver architecture: `Contracts\Driver` with `file` (default), `database` and `array` drivers
- `StashManager`: select the default driver with `STASH_DRIVER`, or per call with `stash('name', driver: 'database')`, `Stash::store('name', 'database')` / `Stash::driver('database')->increment('visits')`
- Custom drivers via `Stash::extend('name', fn ($app) => new MyDriver)`
- `stash:install` command that publishes the config (`--force`) and, with `--database`, the migration of the database driver
- Publishable migration for the database driver (`--tag=stash-migrations`)
- Dot notation for keys (`stash()->put('user.name', 'Mate')`)
- `remember()`, `collect()` and `exists()` methods; `forget()` accepts multiple keys
- Facade now forwards to the default stash and exposes `store()` and `extend()`
- Tests for all drivers, name validation, corrupted files and custom drivers; usage documentation in the README

### Changed

- **Breaking:** requires PHP 8.4+, Laravel 13+ and Pest 5 / Testbench 11 for development
- **Breaking:** config key `stash.path` moved to `stash.drivers.file.path`; added `stash.default`
- **Breaking:** `all()` returns an array instead of `Fluent`
- **Breaking:** `path()` and `fileName()` removed from `Stash` (use `exists()` or `FileDriver::path()`)
- **Breaking:** `increment()` / `decrement()` throw `InvalidArgumentException` on non-numeric values and accept floats
- **Breaking:** dots in keys now mean nested values
- `Stash` is now a `final readonly` class bound to a driver and a name

### Fixed

- Path traversal through the stash name; names are now validated
- Missing storage directory is created automatically
- File writes are atomic (temp file + rename, `LOCK_EX`)
- Corrupted JSON throws a `RuntimeException` instead of failing obscurely or losing data
- `flush()` no longer writes the file just to delete it
- `illuminate/config` constraint was `*`
