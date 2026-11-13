<?php

namespace Ferga\FilamentBuilder\View\Components;

use Illuminate\View\Component;

/**
 * <x-ferga-builder::styles /> — links the published stylesheet
 * (php artisan vendor:publish --tag=ferga-builder-assets) or inlines it.
 */
class Styles extends Component
{
    public function __construct(public bool $inline = false) {}

    public function render(): \Closure
    {
        return function () {
            $published = public_path('vendor/ferga-builder/ferga-builder.css');

            if (! $this->inline && file_exists($published)) {
                return view('ferga-builder::components.raw', ['html' => '<link rel="stylesheet" href="'.e(asset('vendor/ferga-builder/ferga-builder.css')).'?v='.filemtime($published).'">']);
            }

            return view('ferga-builder::components.raw', ['html' => '<style>'.file_get_contents(__DIR__.'/../../../resources/dist/ferga-builder.css').'</style>']);
        };
    }
}
