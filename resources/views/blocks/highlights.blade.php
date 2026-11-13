@php $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($item) => is_array($item) && filled($item['title'] ?? null))); @endphp
@if (count($items))
    <div class="fb-highlights">
        @if (filled($data['title'] ?? null))
            <h2 class="fb-highlights-title">{{ $data['title'] }}</h2>
        @endif
        <div class="fb-grid" style="--fb-grid-columns: {{ in_array($perRow = (int) ($data['columns'] ?? 3), [2, 3, 4], true) ? $perRow : 3 }}">
            @foreach ($items as $item)
                <div class="fb-highlight">
                    @if (filled($item['icon'] ?? null))
                        <span class="fb-highlight-icon">{!! \Ferga\FilamentBuilder\Support\Icons::svg($item['icon']) !!}</span>
                    @endif
                    <h3 class="fb-highlight-title">{{ $item['title'] }}</h3>
                    @if (filled($item['text'] ?? null))
                        <p class="fb-highlight-text">{{ $item['text'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
