<?php

namespace Fibtegis\FilamentInfiniteScroll\Tests\Fixtures;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostsTableDefinition
{
    public static int $perPage = 10;

    public static function configure(Table $table): Table
    {
        return $table
            ->query(Post::query())
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_published'),
            ])
            ->infinite(perPage: static::$perPage);
    }

    public static function view(): string
    {
        return <<<'BLADE'
            <div>{{ $this->table }}</div>
        BLADE;
    }
}
