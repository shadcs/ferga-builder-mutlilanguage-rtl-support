<?php

namespace Ferga\FilamentBuilder\Tests\Fixtures\Blocks;

use Ferga\FilamentBuilder\Blocks\Block;
use Filament\Forms\Components\TextInput;

class CalloutBlock extends Block
{
    public static function type(): string
    {
        return 'callout';
    }

    public function view(): string
    {
        return 'callout-test';
    }

    public function schema(): array
    {
        return [TextInput::make('text')];
    }
}
