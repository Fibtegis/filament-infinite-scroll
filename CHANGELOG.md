# Changelog

All notable changes to `filament-infinite-scroll` will be documented in this file.

## Unreleased (planned 2.0.0)

### Added
- **Filament v5 support.** Filament v4 and v5 share the same table code, so both use the same assets.
- Laravel 13 support.
- Test suite (PHPUnit + Testbench) and GitHub Actions CI for Filament 3, 4 and 5.
- Community files: CONTRIBUTING, SECURITY, CODE_OF_CONDUCT, issue and pull request templates, Dependabot.

### Fixed
- `->infinite()` was not registered on Filament v5 or on dev versions such as `4.x-dev`, because the version was detected by matching the string `v4`/`v3`. Detection now checks for the `Filament\Schemas\Schema` class.
- `->infinite(perPage: 50)` threw an error and `->infinite(50)` was ignored; the batch size is now applied.
- The trait overrode Filament's `updatedTableFilters`, `updatedTableSearch` and `updatedTableSortDirection`, which skipped deferred filters, session persistence and record deselection, and collided with other plugins (#1). On v4/v5 sort changes did not reset the list. Resetting now uses a Livewire lifecycle hook.

### Changed
- Removed the unused `spatie/eloquent-sortable` dependency.
- Dropped Laravel 10 (end of life; Composer blocks it because of security advisories). Laravel 12 and 13 are tested, 11 is still allowed.
- **Breaking:** if you aliased the trait's `updatedTable*` methods (see the old README section on Advanced Tables), remove those aliases.

## 1.1.0 - 2025-07-14 (never tagged; available only as dev-main)

### Added
- **Filament v4 Support**: Added compatibility with Filament v4.x alongside existing v3.2+ support
- **Version Detection**: Added automatic Filament version detection to use appropriate APIs

### Changed
- **PHP Version Requirement**: Bumped minimum PHP version to ^8.2 for Filament v4 compatibility


## 1.0.2 - 2025-06-24
- Fix conflict between advanced tables plugins (@OccTherapist)

## 1.0.0 - 2025-06-22
- Initial release
