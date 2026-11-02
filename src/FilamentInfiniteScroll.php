<?php

namespace Fibtegis\FilamentInfiniteScroll;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Builder;

class FilamentInfiniteScroll
{
    /**
     * Registers the `->infinite()` macro on Filament tables.
     *
     * Filament v4 and v5 share the same table markup and API (v5 only moves
     * to Livewire 4), so they use the same assets. Filament v3 uses a
     * different table container class and gets its own assets.
     */
    public static function configure(): void
    {
        if (! class_exists(Table::class)) {
            return;
        }

        $usesModernTables = static::usesModernTables();

        Table::macro('infinite', function (int $perPage = 25) use ($usesModernTables) {
            /** @var Table $this */
            $this->paginated(false);

            $this->modifyQueryUsing(function (Builder $query, $livewire) use ($perPage) {
                $page = property_exists($livewire, 'page') ? $livewire->page : 1;

                return $query->limit($page * $perPage);
            });

            $livewire = $this->getLivewire();
            $scope = get_class($livewire);

            if (property_exists($livewire, 'infinitePerPage')) {
                $livewire->infinitePerPage = $perPage;
            }

            FilamentView::registerRenderHook(
                PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER,
                fn (): string => FilamentInfiniteScroll::styles($usesModernTables),
                scopes: $scope,
            );

            FilamentView::registerRenderHook(
                PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER,
                function () use ($livewire, $usesModernTables): string {
                    if (property_exists($livewire, 'infiniteEnded') && $livewire->infiniteEnded) {
                        return '';
                    }

                    return FilamentInfiniteScroll::loader($usesModernTables);
                },
                scopes: $scope,
            );

            return $this;
        });
    }

    /**
     * Filament v4 introduced the `filament/schemas` package; v3 does not have it.
     * Detecting by class avoids parsing version strings such as `v5.1.0`,
     * `5.x-dev` or `dev-main`, which broke the previous detection on v5.
     */
    public static function usesModernTables(): bool
    {
        return class_exists(Schema::class);
    }

    public static function containerSelector(bool $usesModernTables): string
    {
        return $usesModernTables ? '.fi-ta-content-ctn' : '.fi-ta-content';
    }

    public static function styles(bool $usesModernTables): string
    {
        if ($usesModernTables) {
            return <<<'HTML'
                <style>
                    .fi-ta-table {
                        position: relative;
                        overflow-y: scroll;
                    }

                    html {
                        overflow: hidden;
                    }

                    .fi-ta-ctn table thead {
                        position: sticky;
                        top: 0;
                        z-index: 9;
                        background-color: white;
                        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
                    }

                    html.dark .fi-ta-ctn table thead {
                        background-color: rgb(31 41 55);
                    }
                </style>
            HTML;
        }

        return <<<'HTML'
            <style>
                .fi-ta-content {
                    position: relative;
                    overflow-y: auto;
                }

                .fi-ta-ctn table thead {
                    position: sticky;
                    top: 0;
                    z-index: 9;
                    background-color: white;
                    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
                }

                html.dark .fi-ta-ctn table thead {
                    background-color: rgb(31 41 55);
                }
            </style>
        HTML;
    }

    public static function loader(bool $usesModernTables): string
    {
        $container = static::containerSelector($usesModernTables);
        $bottomPadding = $usesModernTables ? 8 : 32;
        $spinnerWrapperClass = $usesModernTables ? 'w-full p-4 flex justify-center' : 'w-full text-center p-4';
        $spinnerClass = $usesModernTables ? 'animate-spin h-8 w-8 text-primary-500' : 'animate-spin h-8 w-8 text-primary-500 inline-block';

        return <<<HTML
            <div class="{$spinnerWrapperClass}" wire:loading.block wire:target="loadMore">
                <svg class="{$spinnerClass}" height="24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div wire:ignore
                x-data="{
                    isLoading: false,
                    finished: false,

                    loadMore(sentinel) {
                        if (this.isLoading || this.finished) return;
                        this.isLoading = true;

                        this.\$wire.call('loadMore').then(hasMore => {
                            if (!hasMore) {
                                this.finished = true;
                                sentinel._observer.disconnect();
                                sentinel.remove();
                            }
                            this.isLoading = false;
                        });
                    }
                }"
                x-init="(() => {
                    const container = \$el.closest('[wire\\\\:id]').querySelector('{$container}');

                    if (!document.getElementById('dynamic-table-height')) {
                        const top = container.getBoundingClientRect().top;
                        const bottomPadding = {$bottomPadding};
                        const max = window.innerHeight - top - bottomPadding;

                        const style = document.createElement('style');
                        style.id = 'dynamic-table-height';
                        style.innerHTML = `{$container} { max-height: \${max}px !important; }`;
                        document.head.appendChild(style);
                    }

                    const spinner = \$el.previousElementSibling;
                    container.appendChild(spinner);
                    container.appendChild(\$el);

                    const observer = new IntersectionObserver(entries => {
                        if (entries[0].isIntersecting) \$data.loadMore(\$el);
                    }, { root: container });

                    observer.observe(\$el);
                    \$el._observer = observer;
                })()"
                class="w-full h-1"></div>
        HTML;
    }
}
