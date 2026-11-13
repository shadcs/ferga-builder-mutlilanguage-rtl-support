<div class="fb-media-text {{ ($data['image_position'] ?? 'start') === 'end' ? 'fb-media-end' : 'fb-media-start' }}">
    @php($buttonUrl = \Ferga\FilamentBuilder\Blocks\Block::safeUrl($data['button_url'] ?? null))
    <div class="fb-media-text-media">
        @if ($src)
            <img src="{{ $src }}" alt="{{ $data['alt'] ?? '' }}" loading="lazy" class="fb-img">
        @elseif ($context->editing)
            <div class="fb-placeholder">{{ __('ferga-builder::builder.canvas.no_image') }}</div>
        @endif
    </div>
    <div class="fb-media-text-body">
        @if (filled($data['title'] ?? null))
            <h3 class="fb-media-text-title">{{ $data['title'] }}</h3>
        @endif
        @if (filled($html))
            <div class="fb-prose">{!! $html !!}</div>
        @endif
        @if (filled($data['button_label'] ?? null) && filled($buttonUrl))
            <p><a href="{{ $buttonUrl }}" class="fb-button fb-button-link">{{ $data['button_label'] }} {!! \Ferga\FilamentBuilder\Support\Icons::svg('heroicon-m-arrow-right') !!}</a></p>
        @endif
    </div>
</div>
