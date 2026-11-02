# Contributing

Thanks for helping improve Filament Infinite Scroll! Contributions of all sizes are welcome: bug reports, fixes, docs and new ideas.

## Reporting bugs

Open an issue with the **Bug report** template and include your package, Filament, Laravel and PHP versions, plus the smallest table definition that shows the problem. Security issues must not be reported publicly; see [SECURITY.md](SECURITY.md).

## Development setup

```bash
git clone https://github.com/Fibtegis/filament-infinite-scroll.git
cd filament-infinite-scroll
composer install
composer test
```

To test against a specific Filament major version:

```bash
composer require --no-update "filament/filament:^4.0"   # or ^3.2.118, ^5.0
composer update
composer test
```

The test suite uses [Orchestra Testbench](https://packages.tools/testbench) and a standalone Livewire table (`tests/Fixtures`). CI runs it for Filament 3, 4 and 5 on several Laravel and PHP versions.

## Pull requests

1. Fork the repository and create a branch from `main`.
2. Keep the change focused; one fix or feature per pull request.
3. Add or update tests for the behaviour you change.
4. Run `composer test` and `composer lint` (or `composer format` to fix style with Laravel Pint).
5. Add a line to `CHANGELOG.md` under **Unreleased**.
6. Open the pull request and fill in the template.

### Supported versions

The `main` branch supports Filament v3.2+, v4 and v5 at the same time. Filament v4 and v5 share the same table markup; version specific code lives in `FilamentInfiniteScroll` and is selected by `FilamentInfiniteScroll::usesModernTables()`. Please make sure a change works on all supported versions (CI will tell you).

## Code of conduct

This project follows the [Code of Conduct](CODE_OF_CONDUCT.md). By taking part you agree to follow it.
