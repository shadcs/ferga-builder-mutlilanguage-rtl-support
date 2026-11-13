{{-- Primary + secondary buttons from data.primary_label/url and data.secondary_label/url. --}}
@if (filled($data['primary_label'] ?? null) || filled($data['secondary_label'] ?? null))
    @php($primaryUrl = \Ferga\FilamentBuilder\Blocks\Block::safeUrl($data['primary_url'] ?? null) ?? '#')
    @php($secondaryUrl = \Ferga\FilamentBuilder\Blocks\Block::safeUrl($data['secondary_url'] ?? null) ?? '#')
    <p class="fb-buttons">
        @if (filled($data['primary_label'] ?? null))
            <a href="{{ $primaryUrl }}" class="fb-button fb-button-primary">
                <span>{{ $data['primary_label'] }}</span>
                {!! \Ferga\FilamentBuilder\Support\Icons::svg('heroicon-m-arrow-right') !!}
            </a>
        @endif
        @if (filled($data['secondary_label'] ?? null))
            <a href="{{ $secondaryUrl }}" class="fb-button fb-button-outline"><span>{{ $data['secondary_label'] }}</span></a>
        @endif
    </p>
@endif
