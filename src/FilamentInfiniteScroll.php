<?php

namespace Fibtegis\FilamentInfiniteScroll;

use Closure;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Builder;

class FilamentInfiniteScroll
{
    public static function configure(): void
    {
        Table::mixin(new class {
            public function infinite(int $perPage = 25): Closure
            {
                return function () use ($perPage) {
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

                    if (method_exists($livewire, 'mergeListeners') && method_exists($livewire, 'resetInfinite')) {
                        $livewire->mergeListeners([
                            'updatedTableFilters' => 'resetInfinite',
                            'updatedTableSearch' => 'resetInfinite',
                            'updatedTableSortColumn' => 'resetInfinite',
                            'updatedTableSortDirection' => 'resetInfinite',
                            'resetTableFilters' => 'resetInfinite',
                        ]);
                    }


                    // Kendi div'imizi eklemek yerine, stilleri ve gözlemciyi doğrudan render ediyoruz.
                    // Bu, Filament'in kendi yapısını bozmadan çalışır.
                    FilamentView::registerRenderHook(
                        PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER,
                        function () : string {
                            $stylesAndObserver = <<<HTML
                                <style>
                                     .fi-ta-content {
                                        position: relative;
                                        overflow-y: auto; /* Konteynerin kendisini kaydırılabilir yapıyoruz */
                                    }

                                    .fi-ta-ctn table thead {
                                        position: sticky;
                                        top: 0;
                                        z-index: 9;
                                        background-color: white;
                                        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
                                    }

                                    /* Dark mode için başlık arkaplanı */
                                    html.dark .fi-ta-ctn table thead {
                                        background-color: rgb(31 41 55); /* gray-800 */
                                    }

                                </style>
                            HTML;

                            return $stylesAndObserver;
                        },
                        scopes: $scope,
                    );


                    FilamentView::registerRenderHook(
                        PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, // Bu kanca <table> etiketinin tam kapanışından önce çalışır.
                        function () use ($livewire): string {
                            if (property_exists($livewire, 'infiniteEnded') && $livewire->infiniteEnded) {
                                return '';              // 👈 hiçbir şey döndürme
                            }
                            return <<<'HTML'
                            <div class="w-full text-center p-4" wire:loading.block wire:target="loadMore">
                                    <svg class="animate-spin h-8 w-8 text-primary-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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

                                        this.$wire.call('loadMore').then(hasMore => {
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
                                    const container = $el.closest('[wire\\:id]').querySelector('.fi-ta-content');

                                    if (!document.getElementById('dynamic-table-height')) {
                                        const top = container.getBoundingClientRect().top;
                                        const bottomPadding = 32    ; // altta bırakmak istediğin boşluk
                                        const max = window.innerHeight - top - bottomPadding;

                                        const style = document.createElement('style');
                                        style.id = 'dynamic-table-height';
                                        // Stili, Livewire'ın silemeyeceği <head> içine ekliyoruz.
                                        style.innerHTML = `.fi-ta-content { max-height: ${max}px !important; }`;
                                        document.head.appendChild(style);
                                    }

                                    const spinner   = $el.previousElementSibling;
                                    container.appendChild(spinner);
                                    container.appendChild($el);

                                    const observer = new IntersectionObserver(entries => {
                                        if (entries[0].isIntersecting) $data.loadMore($el);
                                    }, { root: container });

                                    observer.observe($el);
                                    $el._observer = observer;
                                })()"
                                     class="w-full h-1"></div>
                            HTML;
                        },
                        scopes: $scope,
                    );

                    return $this;
                };
            }
        });
    }
}
