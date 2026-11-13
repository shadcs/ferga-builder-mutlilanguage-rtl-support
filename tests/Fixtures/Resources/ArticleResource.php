<?php

namespace Ferga\FilamentBuilder\Tests\Fixtures\Resources;

use Ferga\FilamentBuilder\Filament\Pages\BuilderCanvas;
use Ferga\FilamentBuilder\Forms\Components\PageBuilder;
use Ferga\FilamentBuilder\Tests\Fixtures\Models\Article;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required(),
            PageBuilder::make('body'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ArticleList::route('/'),
            'create' => ArticleCreate::route('/create'),
            'edit' => ArticleEdit::route('/{record}/edit'),
            'builder' => ArticleCanvas::route('/{record}/builder'),
        ];
    }
}

class ArticleList extends ListRecords
{
    protected static string $resource = ArticleResource::class;
}

class ArticleCreate extends CreateRecord
{
    protected static string $resource = ArticleResource::class;
}

class ArticleEdit extends EditRecord
{
    protected static string $resource = ArticleResource::class;
}

class ArticleCanvas extends BuilderCanvas
{
    protected static string $resource = ArticleResource::class;

    protected static string $contentField = 'body';
}
