

# Filament Infinite Scroll v3

**Filament Infinite Scroll** is a lightweight plugin that replaces the standard Filament **v3** table pagination with a fluid, auto-loading infinite scroll mechanism. It is compatible with all tables, including Resources, Relation Managers, Widgets, and standalone Table Builders.

As the user approaches the end of a list, more rows are fetched transparently via Livewire, preserving filters, search, and sorting.

> Developed and maintained by **Fibtegis Yazılım Proje ve Danışmanık Hizmetleri LTD. ŞTİ.**
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
| Laravel     | **10 or 11**   |
| Filament    | **3.0+**       |
| Livewire    | Ships with Filament |
| Alpine.js   | Ships with Filament |

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
    Add the `InteractsWithInfiniteScroll` trait to your respective `ListRecords` page, relation manager, or table widget.

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
*   The `InteractsWithInfiniteScroll` **Trait** tracks the current `$page` number and whether all records have been loaded (`$infiniteEnded`).
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

&copy; 2025 **Fibtegis Yazılım Proje ve Danışmanık Hizmetleri LTD. ŞTİ.**

---

## ⚠️ Trait Collision: InteractsWithInfiniteScroll vs AdvancedTables

If you're using both `InteractsWithInfiniteScroll` and `AdvancedTables` traits in the same class (such as `ListRecords`), method name collisions like `updatedTableFilters`, `updatedTableSearch`, and `updatedTableSortColumn` may occur.

To resolve this, explicitly alias the methods and define a custom implementation that calls both versions:

```php
use InteractsWithInfiniteScroll, AdvancedTables {
    InteractsWithInfiniteScroll::updatedTableFilters as infiniteUpdatedTableFilters;
    InteractsWithInfiniteScroll::updatedTableSearch as infiniteUpdatedTableSearch;
    InteractsWithInfiniteScroll::updatedTableSortColumn as infiniteUpdatedTableSortColumn;
    AdvancedTables::updatedTableFilters as advancedUpdatedTableFilters;
    AdvancedTables::updatedTableSearch as advancedUpdatedTableSearch;
    AdvancedTables::updatedTableSortColumn as advancedUpdatedTableSortColumn;
}

public function updatedTableFilters(): void
{
    $this->infiniteUpdatedTableFilters();
    $this->advancedUpdatedTableFilters();
}

public function updatedTableSearch(): void
{
    $this->infiniteUpdatedTableSearch();
    $this->advancedUpdatedTableSearch();
}

public function updatedTableSortColumn(): void
{
    $this->infiniteUpdatedTableSortColumn();
    $this->advancedUpdatedTableSortColumn();
}
```

This ensures both plugins work together without conflict.

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
