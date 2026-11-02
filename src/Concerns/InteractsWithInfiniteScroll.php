<?php

/*
 * This file is part of the Filament Infinite Scroll package.
 *
 * (c) İsmail KÖSE
 * ismail@fbt.gs
 * */

namespace Fibtegis\FilamentInfiniteScroll\Concerns;

trait InteractsWithInfiniteScroll
{
    public int $page = 1;

    public int $infinitePerPage = 25;

    public bool $infiniteEnded = false;

    public ?string $infiniteStateHash = null;

    public function loadMore(): bool
    {
        if ($this->infiniteEnded) {
            return false;
        }

        $this->page++;

        $totalRecords = $this->getFilteredTableQuery()->count();
        $loadedRecords = $this->page * $this->infinitePerPage;

        if ($loadedRecords >= $totalRecords) {
            $this->infiniteEnded = true;

            return false;
        }

        return true;
    }

    protected function resetInfinite(): void
    {
        $this->page = 1;
        $this->infiniteEnded = false;
    }

    /**
     * Livewire calls `rendering{TraitName}` before every render, so this runs
     * without overriding any Filament method (such as `updatedTableFilters`).
     * Overriding those methods skipped Filament's own logic and collided with
     * other plugins, see issue #1.
     *
     * When the search, filters, sort or grouping changed since the previous
     * render (from the browser or from server side code such as the filter
     * reset action), the list starts again from the first batch.
     */
    public function renderingInteractsWithInfiniteScroll(): void
    {
        $hash = $this->getInfiniteStateHash();

        if ($this->infiniteStateHash !== null && $this->infiniteStateHash !== $hash) {
            $this->resetInfinite();
        }

        $this->infiniteStateHash = $hash;
    }

    protected function getInfiniteStateHash(): string
    {
        $state = [];

        foreach ([
            'tableSearch',
            'tableColumnSearches',
            'tableFilters',
            'tableSort',
            'tableSortColumn',
            'tableSortDirection',
            'tableGrouping',
            'tableGroupingDirection',
        ] as $property) {
            if (property_exists($this, $property)) {
                $state[$property] = $this->{$property};
            }
        }

        return md5(json_encode($state) ?: serialize($state));
    }
}
