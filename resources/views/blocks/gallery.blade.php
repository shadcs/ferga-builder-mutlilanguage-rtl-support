@if (count($images))
    <div class="fb-gallery" style="--fb-gallery-columns: {{ $columns }}">
        @foreach ($images as $src)
            <a href="{{ \Ferga\FilamentBuilder\Blocks\Block::safeUrl($src) ?? '#' }}" class="fb-gallery-item" target="_blank" rel="noopener">
                <img src="{{ $src }}" alt="{{ $data['alt'] ?? '' }}" loading="lazy" class="fb-img">
            </a>
        @endforeach
    </div>
@elseif ($context->editing)
    <div class="fb-placeholder">{{ __('ferga-builder::builder.canvas.no_images') }}</div>
@endif
