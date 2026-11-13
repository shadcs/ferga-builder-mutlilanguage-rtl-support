<?php

namespace Ferga\FilamentBuilder\Tests\Fixtures\Resources;

use Ferga\FilamentBuilder\Forms\Components\PageBuilder;
use Ferga\FilamentBuilder\Tests\Fixtures\Models\Page;
use Ferga\FilamentBuilder\Tests\Fixtures\Resources\PageResource\Pages;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    public static ?array $limitLocales = null;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required(),
            PageBuilder::make('content')->locales(fn () => static::$limitLocales),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
            'builder' => Pages\PageCanvas::route('/{record}/builder'),
        ];
    }
}
