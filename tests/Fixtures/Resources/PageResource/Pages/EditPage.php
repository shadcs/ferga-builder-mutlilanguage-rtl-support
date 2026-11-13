<?php

namespace Ferga\FilamentBuilder\Tests\Fixtures\Resources\PageResource\Pages;

use Ferga\FilamentBuilder\Filament\Actions\OpenCanvasAction;
use Ferga\FilamentBuilder\Tests\Fixtures\Resources\PageResource;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [OpenCanvasAction::make()];
    }
}
