# Filament Infinite Scroll

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fibtegis/filament-infinite-scroll.svg?style=flat-square)](https://packagist.org/packages/fibtegis/filament-infinite-scroll)
[![Tests](https://img.shields.io/github/actions/workflow/status/Fibtegis/filament-infinite-scroll/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Fibtegis/filament-infinite-scroll/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/fibtegis/filament-infinite-scroll.svg?style=flat-square)](https://packagist.org/packages/fibtegis/filament-infinite-scroll)
[![License](https://img.shields.io/github/license/Fibtegis/filament-infinite-scroll?style=flat-square)](LICENSE)

**Filament Infinite Scroll** is a lightweight plugin that replaces the standard Filament table pagination with a fluid, auto-loading infinite scroll mechanism. It works with **Filament v3.2+, v4 and v5** from the same release.

As the user approaches the end of a list, more rows are fetched transparently via Livewire, preserving filters, search, and sorting.

> Developed and maintained by **Fibtegis Yazılım Proje ve Danışmanlık Hizmetleri LTD. ŞTİ.**
> https://fbt.gs

---


## Features

*   🔄 **Seamless Integration:** Smooth infinite scroll on any Filament table.
*   🪄 **One-Line Setup:** Enable it by simply adding the `->infinite()` method.
*   🏎️ **Efficient Server Usage:** Low server memory usage as records are fetched in batches.
*   🌗 **Theme-Aware:** Works in both dark and light modes, inheriting your panel's theme.
*   📏 **Auto-Height:** Automatically calculates the table height once and keeps it fixed for the session, making only the table body scrollable.
*   ⚙️ **Auto-Reset:** Automatically resets when the user changes filters, search, or sorting.
*   🛠️ **Modern Standards:** Fully PSR-4 compliant, installable via Composer, and auto-discoverable by the panel.

---

## Requirements

| Package     | Version        |
|-------------|----------------|
| PHP         | **8.2+**       |
| Laravel     | **12 or 13** (11 is allowed but end of life and not tested) |
| Filament    | **3.2+, 4.x or 5.x** |
| Livewire    | Ships with Filament (v3 for Filament 3/4, v4 for Filament 5) |
| Alpine.js   | Ships with Filament |

### Where it works

| Table type | Status |
|------------|--------|
| Resource list pages (`ListRecords`) | ✅ Supported |
| Relation managers, table widgets, standalone Livewire tables | 🚧 Not yet: the query is limited but the loader is only rendered on list pages. Tracked for the next release. |

---

## Installation

Install the package into your project via Composer:

```bash
composer require fibtegis/filament-infinite-scroll
```

After installation, it's good practice to clear cached views and plugins:

```bash
php artisan optimize:clear
```

The plugin is auto-discovered by Filament. No manual registration is needed.

---

## Quick Start

Enabling infinite scroll is just a two-step process:

1.  **Use the Trait:**
    Add the `InteractsWithInfiniteScroll` trait to your resource's `ListRecords` page.

    ```php
    // app/Filament/Resources/CustomerResource/Pages/ListCustomers.php

    use Fibtegis\FilamentInfiniteScroll\Concerns\InteractsWithInfiniteScroll;
    use Filament\Resources\Pages\ListRecords;

    class ListCustomers extends ListRecords
    {
        use InteractsWithInfiniteScroll;

        // ...
    }
    ```

2.  **Enable the Method:**
    Inside your `table()` method, chain the `->infinite()` method to your table definition.

    ```php
    // app/Filament/Resources/CustomerResource.php

    use Filament\Tables\Table;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ... your columns ...
            ])
            ->filters([
                // ... your filters ...
            ])
            ->actions([
                // ... your actions ...
            ])
            ->bulkActions([
                // ... your bulk actions ...
            ])
            ->infinite(); // 👈 Just add this line
    }
    ```

That's it! Your table will now automatically load new records when you scroll to the bottom.

---

## Configuration

The `infinite()` method accepts a parameter to define the number of records to load per batch.

```php
->infinite(perPage: 50) // Load 50 records at a time (default: 25)
```

| Option    | Default | Description                               |
|-----------|---------|-------------------------------------------|
| `perPage` | `25`    | The number of records to fetch in each batch. |

---

## How It Works

*   A `Table::mixin` adds the `->infinite()` method. This method disables native pagination and limits the query based on the current page.
*   The `InteractsWithInfiniteScroll` **Trait** tracks the current `$page` number and whether all records have been loaded (`$infiniteEnded`). It uses a Livewire lifecycle hook (`renderingInteractsWithInfiniteScroll`) to start again from the first batch when search, filters, sorting or grouping change, so it does not override any Filament method.
*   An **Alpine.js** component uses an `IntersectionObserver` to detect when the end of the page is reached.
*   When the end of the page is reached, the `loadMore()` method is triggered via Livewire.
*   On initial page load, Alpine.js calculates the maximum height of the table container **once** and injects this style into the page's `<head>` tag. This ensures the style is permanent and unaffected by Livewire updates.
*   When the records run out (`$infiniteEnded = true`), the observer is automatically stopped.

---

## 💖 Supporting the Project

If you find this plugin useful and would like to support its future development, you can do so by simply acquiring our company's token. Your support is greatly appreciated and helps us continue to maintain and improve this project.

- **Token:** Fibtegis Token (Solana)
- **Contract:** `CXun4JaKiDLmPHrcMLbB2v9ZEqXztp7SsRtgHpmYpump`
- **Buy/View on GMGN:** [https://gmgn.ai/sol/token/CXun4JaKiDLmPHrcMLbB2v9ZEqXztp7SsRtgHpmYpump](https://gmgn.ai/sol/token/CXun4JaKiDLmPHrcMLbB2v9ZEqXztp7SsRtgHpmYpump?filter=All)

---

## License

Published under the Apache-2.0 License. See the `LICENSE` file for details.

&copy; 2025 **Fibtegis Yazılım Proje ve Danışmanlık Hizmetleri LTD. ŞTİ.**

---

## Upgrading from 1.x

*   Version 2 no longer overrides `updatedTableFilters`, `updatedTableSearch`, `updatedTableSortColumn`, `updatedTableSortDirection` or `resetTableFilters`. If you added an alias block to combine this trait with another plugin (such as Advanced Tables), **remove the `InteractsWithInfiniteScroll::...` aliases and the wrapper methods**; the traits no longer collide.
*   `->infinite(perPage: 50)` now works as documented. In 1.x the value was ignored and 25 rows were always loaded.

---

## Testing

```bash
composer test
```

See [CONTRIBUTING.md](CONTRIBUTING.md) for running the suite against a specific Filament version.

## Contributing & Security

Contributions are welcome, see [CONTRIBUTING.md](CONTRIBUTING.md). Please report security issues privately as described in [SECURITY.md](SECURITY.md).

## Contributors ✨

Thanks goes to these wonderful people:

<!-- ALL-CONTRIBUTORS-LIST:START -->
<!-- prettier-ignore -->
<table>
  <tr>
    <td align="center"><a href="https://github.com/OccTherapist"><img src="https://avatars.githubusercontent.com/OccTherapist" width="100px;" alt=""/><br /><sub><b>OccTherapist</b></sub></a><br /><a href="#code-OccTherapist" title="Code">💻</a></td>
  </tr>
</table>
<!-- ALL-CONTRIBUTORS-LIST:END -->
