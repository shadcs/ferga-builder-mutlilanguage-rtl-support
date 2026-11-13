@if ($src)
    <figure class="fb-figure">
        @php($link = \Ferga\FilamentBuilder\Blocks\Block::safeUrl($data['link'] ?? null))
        @if (filled($link))<a href="{{ $link }}" class="fb-figure-link">@endif
        <img src="{{ $src }}" alt="{{ $data['alt'] ?? '' }}" loading="lazy" class="fb-img">
        @if (filled($link))</a>@endif
        @if (filled($data['caption'] ?? null))
            <figcaption class="fb-caption">{{ $data['caption'] }}</figcaption>
        @endif
    </figure>
@elseif ($context->editing)
    <div class="fb-placeholder">{{ __('ferga-builder::builder.canvas.no_image') }}</div>
@endif
