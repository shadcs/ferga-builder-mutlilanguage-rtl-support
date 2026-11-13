@if (filled($data['title'] ?? null))
    <section class="fb-cta">
        <div class="fb-cta-body">
            <h2 class="fb-cta-title">{{ $data['title'] }}</h2>
            @if (filled($data['text'] ?? null))
                <p class="fb-cta-text">{{ $data['text'] }}</p>
            @endif
        </div>
        @include('ferga-builder::partials.buttons')
    </section>
@endif
