@if (count($items))
    <div class="fb-cards">
        @if (filled($data['title'] ?? null))
            <h2 class="fb-section-title">{{ $data['title'] }}</h2>
        @endif
        <div class="fb-grid" style="--fb-grid-columns: {{ $columns }}">
            @foreach ($items as $item)
                <article class="fb-card">
                    @if ($item['src'])
                        <img src="{{ $item['src'] }}" alt="" class="fb-card-image" loading="lazy">
                    @endif
                    <div class="fb-card-body">
                        <h3 class="fb-card-title">{{ $item['title'] }}</h3>
                        @if (filled($item['text'] ?? null))
                            <p class="fb-card-text">{{ $item['text'] }}</p>
                        @endif
                        @php($url = \Ferga\FilamentBuilder\Blocks\Block::safeUrl($item['url'] ?? null))
                        @if (filled($url) && filled($item['link_label'] ?? null))
                            <a href="{{ $url }}" class="fb-button fb-button-link fb-card-link">
                                <span>{{ $item['link_label'] }}</span>
                                {!! \Ferga\FilamentBuilder\Support\Icons::svg('heroicon-m-arrow-right') !!}
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endif
