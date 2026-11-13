@if (filled($data['label'] ?? null))
    @php $variant = in_array($data['variant'] ?? 'primary', ['primary', 'outline', 'link'], true) ? ($data['variant'] ?? 'primary') : 'primary'; @endphp
    <p class="fb-buttons">
        <a href="{{ $data['url'] ?? '#' }}" class="fb-button fb-button-{{ $variant }}" @if ($data['new_tab'] ?? false) target="_blank" rel="noopener" @endif>
            <span>{{ $data['label'] }}</span>
            @if ($data['arrow'] ?? false)
                {!! \Ferga\FilamentBuilder\Support\Icons::svg('heroicon-m-arrow-right') !!}
            @endif
        </a>
    </p>
@endif
