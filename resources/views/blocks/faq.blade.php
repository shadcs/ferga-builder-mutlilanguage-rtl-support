@php
    $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($item) => is_array($item) && filled($item['question'] ?? null)));
@endphp
@if (count($items))
    <div class="fb-faq">
        @if (filled($data['title'] ?? null))
            <h2 class="fb-section-title">{{ $data['title'] }}</h2>
        @endif
        <div class="fb-faq-list">
            @foreach ($items as $item)
                <details class="fb-faq-item" @if ($loop->first && ($data['open_first'] ?? false)) open @endif>
                    <summary class="fb-faq-question">
                        <span>{{ $item['question'] }}</span>
                        {!! \Ferga\FilamentBuilder\Support\Icons::svg('heroicon-m-chevron-down', 'fb-icon fb-faq-chevron') !!}
                    </summary>
                    <div class="fb-faq-answer">{!! nl2br(e($item['answer'] ?? '')) !!}</div>
                </details>
            @endforeach
        </div>
    </div>
@endif
