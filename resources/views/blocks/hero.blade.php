@if (filled($data['title'] ?? null))
    <section class="fb-hero fb-hero-{{ $layout }}" @if ($layout === 'cover' && $src) style="--fb-hero-image: url('{{ $src }}')" @endif>
        <div class="fb-hero-body">
            @if (filled($data['eyebrow'] ?? null))
                <p class="fb-eyebrow">{{ $data['eyebrow'] }}</p>
            @endif
            <h1 class="fb-hero-title">{{ $data['title'] }}</h1>
            @if (filled($data['text'] ?? null))
                <p class="fb-hero-text">{{ $data['text'] }}</p>
            @endif
            @include('ferga-builder::partials.buttons')
        </div>
        @if ($layout === 'split')
            <div class="fb-hero-media">
                @if ($src)
                    <img src="{{ $src }}" alt="{{ $data['alt'] ?? '' }}" class="fb-img" loading="eager">
                @elseif ($context->editing)
                    <div class="fb-placeholder">{{ __('ferga-builder::builder.canvas.no_image') }}</div>
                @endif
            </div>
        @endif
    </section>
@endif
