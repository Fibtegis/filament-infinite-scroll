<?php

namespace Fibtegis\FilamentInfiniteScroll\Tests;

use Composer\InstalledVersions;
use Fibtegis\FilamentInfiniteScroll\FilamentInfiniteScroll;
use Fibtegis\FilamentInfiniteScroll\Tests\Fixtures\LegacyPostsTable;
use Fibtegis\FilamentInfiniteScroll\Tests\Fixtures\PostsTable;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class InfiniteScrollTest extends TestCase
{
    protected function tableComponent(): string
    {
        return FilamentInfiniteScroll::usesModernTables() ? PostsTable::class : LegacyPostsTable::class;
    }

    protected function visibleRecords(Testable $component): int
    {
        return $component->instance()->getTableRecords()->count();
    }

    #[Test]
    public function it_registers_the_infinite_macro(): void
    {
        $this->assertTrue(Table::hasMacro('infinite'));
    }

    #[Test]
    public function it_detects_the_installed_filament_generation(): void
    {
        $major = (int) ltrim(InstalledVersions::getPrettyVersion('filament/tables') ?? '0', 'v');

        if ($major === 0) {
            $this->markTestSkipped('Filament is installed from a dev branch.');
        }

        $this->assertSame($major >= 4, FilamentInfiniteScroll::usesModernTables());
    }

    #[Test]
    public function it_loads_records_in_batches_of_per_page(): void
    {
        $this->createPosts(25);

        $component = Livewire::test($this->tableComponent());

        $this->assertSame(10, $this->visibleRecords($component));
        $this->assertSame(10, $component->get('infinitePerPage'));

        $component->call('loadMore')->assertReturned(true);
        $this->assertSame(2, $component->get('page'));
        $this->assertSame(20, $this->visibleRecords($component));
        $component->assertSet('infiniteEnded', false);

        $component->call('loadMore')->assertReturned(false);
        $this->assertSame(25, $this->visibleRecords($component));
        $component->assertSet('infiniteEnded', true);

        $component->call('loadMore')->assertReturned(false);
        $component->assertSet('page', 3);
    }

    #[Test]
    public function it_resets_when_the_search_changes(): void
    {
        $this->createPosts(25);

        $component = Livewire::test($this->tableComponent())
            ->call('loadMore')
            ->call('loadMore')
            ->assertSet('infiniteEnded', true)
            ->set('tableSearch', 'Post 0')
            ->assertSet('page', 1)
            ->assertSet('infiniteEnded', false);

        $this->assertSame(10, $this->visibleRecords($component));
    }

    #[Test]
    public function it_resets_when_a_filter_changes(): void
    {
        $this->createPosts(25);

        Livewire::test($this->tableComponent())
            ->call('loadMore')
            ->assertSet('page', 2)
            ->set('tableFilters.is_published.value', '1')
            ->assertSet('page', 1);
    }

    #[Test]
    public function it_resets_when_the_sort_changes(): void
    {
        $this->createPosts(25);

        Livewire::test($this->tableComponent())
            ->call('loadMore')
            ->assertSet('page', 2)
            ->call('sortTable', 'title', 'desc')
            ->assertSet('page', 1);
    }

    #[Test]
    public function it_does_not_reset_while_scrolling(): void
    {
        $this->createPosts(25);

        Livewire::test($this->tableComponent())
            ->call('loadMore')
            ->call('$refresh')
            ->assertSet('page', 2);
    }

    #[Test]
    public function it_keeps_filament_filter_handling(): void
    {
        $this->createPosts(25);

        $component = Livewire::test($this->tableComponent())
            ->set('tableFilters.is_published.value', '1');

        $this->assertSame(10, $this->visibleRecords($component));
        $this->assertTrue($component->instance()->getTableRecords()->every(fn ($post) => $post->is_published));
    }

    #[Test]
    public function it_renders_the_loader_for_the_installed_version(): void
    {
        $this->createPosts(25);

        Livewire::test($this->tableComponent());

        $html = FilamentView::renderHook(
            PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER,
            scopes: $this->tableComponent(),
        )->toHtml();

        $container = FilamentInfiniteScroll::containerSelector(FilamentInfiniteScroll::usesModernTables());

        $this->assertStringContainsString("querySelector('{$container}')", $html);
        $this->assertStringContainsString("\$wire.call('loadMore')", $html);
        $this->assertStringContainsString("closest('[wire\\\\:id]')", $html);
        $this->assertStringContainsString('max-height: ${max}px', $html);
        $this->assertStringContainsString('<style>', $html);
    }
}
