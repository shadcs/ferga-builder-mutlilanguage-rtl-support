<?php

namespace Ferga\FilamentBuilder;

use Ferga\FilamentBuilder\Rendering\BlockRenderer;
use Ferga\FilamentBuilder\Templates\CoreTemplates;
use Ferga\FilamentBuilder\Templates\TemplateRegistry;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FergaBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ferga-builder.php', 'ferga-builder');

        $this->app->singleton(BlockRegistry::class, function () {
            return (new BlockRegistry)->registerMany((array) config('ferga-builder.blocks', []));
        });

        $this->app->singleton(TemplateRegistry::class, function () {
            $registry = new TemplateRegistry;

            return config('ferga-builder.core_templates', true) ? $registry->registerMany(CoreTemplates::all()) : $registry;
        });

        $this->app->singleton(FergaBuilder::class);
        $this->app->singleton(BlockRenderer::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ferga-builder');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'ferga-builder');

        Blade::componentNamespace('Ferga\\FilamentBuilder\\View\\Components', 'ferga-builder');

        FilamentAsset::register([
            Css::make('ferga-builder', __DIR__.'/../resources/dist/ferga-builder.css'),
            Css::make('ferga-builder-editor', __DIR__.'/../resources/dist/ferga-builder-editor.css'),
        ], 'ferga/filament-builder');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ferga-builder.php' => config_path('ferga-builder.php'),
            ], 'ferga-builder-config');

            $this->publishes([
                __DIR__.'/../resources/dist/ferga-builder.css' => public_path('vendor/ferga-builder/ferga-builder.css'),
            ], 'ferga-builder-assets');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/ferga-builder'),
            ], 'ferga-builder-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/ferga-builder'),
            ], 'ferga-builder-translations');
        }
    }
}
