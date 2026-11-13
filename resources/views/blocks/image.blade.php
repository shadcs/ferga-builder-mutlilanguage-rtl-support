@if ($src)
    <figure class="fb-figure">
        @if (filled($data['link'] ?? null))<a href="{{ $data['link'] }}" class="fb-figure-link">@endif
        <img src="{{ $src }}" alt="{{ $data['alt'] ?? '' }}" loading="lazy" class="fb-img">
        @if (filled($data['link'] ?? null))</a>@endif
        @if (filled($data['caption'] ?? null))
            <figcaption class="fb-caption">{{ $data['caption'] }}</figcaption>
        @endif
    </figure>
@elseif ($context->editing)
    <div class="fb-placeholder">{{ __('ferga-builder::builder.canvas.no_image') }}</div>
@endif
