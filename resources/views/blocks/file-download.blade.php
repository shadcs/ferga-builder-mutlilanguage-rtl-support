@php($safeHref = \Ferga\FilamentBuilder\Blocks\Block::safeUrl($href))
@if ($safeHref)
    <a href="{{ $safeHref }}" class="fb-file" download>
        <span class="fb-file-icon">{!! \Ferga\FilamentBuilder\Support\Icons::svg('heroicon-o-arrow-down-tray') !!}</span>
        <span class="fb-file-body">
            <span class="fb-file-label">{{ $data['label'] ?? basename((string) ($data['file'] ?? '')) }}</span>
            @if (filled($data['description'] ?? null))
                <span class="fb-file-description">{{ $data['description'] }}</span>
            @endif
        </span>
        @if ($extension || $size)
            <span class="fb-file-meta">{{ collect([$extension, $size])->filter()->implode(' · ') }}</span>
        @endif
    </a>
@elseif ($context->editing)
    <div class="fb-placeholder">{{ __('ferga-builder::builder.canvas.no_file') }}</div>
@endif
