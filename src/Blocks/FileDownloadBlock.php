<?php

namespace Ferga\FilamentBuilder\Blocks;

use BackedEnum;
use Ferga\FilamentBuilder\FergaBuilder;
use Ferga\FilamentBuilder\Rendering\RenderContext;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Throwable;

class FileDownloadBlock extends Block
{
    public static function type(): string
    {
        return 'file_download';
    }

    public function icon(): string|BackedEnum
    {
        return Heroicon::OutlinedArrowDownTray;
    }

    public function fileFields(): array
    {
        return ['file'];
    }

    public function preview(array $data): ?string
    {
        return static::excerpt($data['label'] ?? null);
    }

    public function schema(): array
    {
        return [
            $this->upload('file')
                ->acceptedFileTypes([
                    'application/pdf',
                    'text/plain',
                    'text/csv',
                    'application/rtf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                ])
                ->maxSize(51200)
                ->required(),
            TextInput::make('label')->label(static::t('fields.label'))->required(),
            TextInput::make('description')->label(static::t('fields.description')),
        ];
    }

    public function viewData(array $data, RenderContext $context): array
    {
        $builder = app(FergaBuilder::class);
        $path = is_string($data['file'] ?? null) ? $data['file'] : null;
        $size = null;

        if ($path && ! str_contains($path, '://')) {
            try {
                $disk = Storage::disk($builder->disk());
                $size = $disk->exists($path) ? $disk->size($path) : null;
            } catch (Throwable) {
            }
        }

        return [
            'href' => $builder->url($path),
            'extension' => $path ? strtoupper(pathinfo($path, PATHINFO_EXTENSION)) : null,
            'size' => $size === null ? null : static::humanSize($size),
        ];
    }

    public static function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $i === 0 ? 0 : 1).' '.$units[$i];
    }
}
