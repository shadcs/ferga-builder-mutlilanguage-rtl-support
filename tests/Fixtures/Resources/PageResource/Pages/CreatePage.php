<?php

namespace Ferga\FilamentBuilder\Tests\Fixtures\Resources\PageResource\Pages;

use Ferga\FilamentBuilder\Tests\Fixtures\Resources\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;
}
