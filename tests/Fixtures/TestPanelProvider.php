<?php

namespace Ferga\FilamentBuilder\Tests\Fixtures;

use Ferga\FilamentBuilder\FergaBuilderPlugin;
use Ferga\FilamentBuilder\Tests\Fixtures\Resources\ArticleResource;
use Ferga\FilamentBuilder\Tests\Fixtures\Resources\PageResource;
use Filament\Panel;
use Filament\PanelProvider;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->resources([PageResource::class, ArticleResource::class])
            ->plugin(
                FergaBuilderPlugin::make()
                    ->locales(['en' => 'English', 'ar' => 'العربية', 'ku' => 'کوردی', 'cn' => '中文'])
                    ->defaultLocale('en')
            );
    }
}
