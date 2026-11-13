@if ($embed)
    <figure class="fb-figure">
        <div class="fb-video" style="aspect-ratio: {{ $ratio }}">
            @if ($context->editing)
                <div class="fb-video-cover">▶ {{ $data['url'] }}</div>
            @else
                <iframe src="{{ $embed }}" title="{{ $data['caption'] ?? 'Video' }}" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
            @endif
        </div>
        @if (filled($data['caption'] ?? null))
            <figcaption class="fb-caption">{{ $data['caption'] }}</figcaption>
        @endif
    </figure>
@elseif ($context->editing)
    <div class="fb-placeholder">{{ __('ferga-builder::builder.canvas.no_video') }}</div>
@endif
