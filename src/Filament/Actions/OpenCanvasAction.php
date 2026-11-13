<?php

namespace Ferga\FilamentBuilder\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

/**
 * Header action linking a record to its visual canvas page.
 *
 *   OpenCanvasAction::make()           // resource page named "builder"
 *   OpenCanvasAction::make()->page('canvas')
 */
class OpenCanvasAction extends Action
{
    protected string $canvasPage = 'builder';

    public static function getDefaultName(): ?string
    {
        return 'openFergaBuilderCanvas';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('ferga-builder::builder.actions.open_canvas'));
        $this->icon(Heroicon::OutlinedPaintBrush);
        $this->color('gray');
        $this->url(fn (?Model $record, $livewire): ?string => $record && method_exists($livewire, 'getResource')
            ? $livewire::getResource()::getUrl($this->canvasPage, ['record' => $record])
            : null);
    }

    public function page(string $name): static
    {
        $this->canvasPage = $name;

        return $this;
    }
}
