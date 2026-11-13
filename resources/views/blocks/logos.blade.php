@if (count($images))
    <div class="fb-logos @if ($data['grayscale'] ?? true) fb-logos-grayscale @endif">
        @if (filled($data['title'] ?? null))
            <p class="fb-logos-title">{{ $data['title'] }}</p>
        @endif
        <div class="fb-logos-row">
            @foreach ($images as $src)
                <img src="{{ $src }}" alt="" class="fb-logo" loading="lazy">
            @endforeach
        </div>
    </div>
@elseif ($context->editing)
    <div class="fb-placeholder">{{ __('ferga-builder::builder.canvas.no_images') }}</div>
@endif
