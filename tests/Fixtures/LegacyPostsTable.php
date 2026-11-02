<?php

namespace Fibtegis\FilamentInfiniteScroll\Tests\Fixtures;

use Fibtegis\FilamentInfiniteScroll\Concerns\InteractsWithInfiniteScroll;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

/**
 * Standalone table for Filament v3.
 */
class LegacyPostsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithInfiniteScroll;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return PostsTableDefinition::configure($table);
    }

    public function render(): string
    {
        return PostsTableDefinition::view();
    }
}
