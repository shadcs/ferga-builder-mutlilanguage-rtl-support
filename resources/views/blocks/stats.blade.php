@php $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($item) => is_array($item) && filled($item['value'] ?? null))); @endphp
@if (count($items))
    <dl class="fb-stats" style="--fb-grid-columns: {{ min(count($items), 4) }}">
        @foreach ($items as $item)
            <div class="fb-stat">
                <dt class="fb-stat-label">{{ $item['label'] ?? '' }}</dt>
                <dd class="fb-stat-value">{{ $item['value'] }}</dd>
            </div>
        @endforeach
    </dl>
@endif
