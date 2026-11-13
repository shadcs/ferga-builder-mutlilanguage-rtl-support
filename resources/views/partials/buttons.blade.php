{{-- Primary + secondary buttons from data.primary_label/url and data.secondary_label/url. --}}
@if (filled($data['primary_label'] ?? null) || filled($data['secondary_label'] ?? null))
    <p class="fb-buttons">
        @if (filled($data['primary_label'] ?? null))
            <a href="{{ $data['primary_url'] ?? '#' }}" class="fb-button fb-button-primary">
                <span>{{ $data['primary_label'] }}</span>
                {!! \Ferga\FilamentBuilder\Support\Icons::svg('heroicon-m-arrow-right') !!}
            </a>
        @endif
        @if (filled($data['secondary_label'] ?? null))
            <a href="{{ $data['secondary_url'] ?? '#' }}" class="fb-button fb-button-outline"><span>{{ $data['secondary_label'] }}</span></a>
        @endif
    </p>
@endif
