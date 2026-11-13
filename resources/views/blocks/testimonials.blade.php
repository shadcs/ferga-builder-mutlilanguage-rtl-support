@if (count($items))
    <div class="fb-testimonials">
        @if (filled($data['title'] ?? null))
            <h2 class="fb-section-title">{{ $data['title'] }}</h2>
        @endif
        <div class="fb-grid" style="--fb-grid-columns: {{ $columns }}">
            @foreach ($items as $item)
                <figure class="fb-testimonial">
                    <blockquote class="fb-testimonial-quote">{!! nl2br(e($item['quote'])) !!}</blockquote>
                    <figcaption class="fb-testimonial-person">
                        @if ($item['src'])
                            <img src="{{ $item['src'] }}" alt="" class="fb-avatar" loading="lazy">
                        @else
                            <span class="fb-avatar fb-avatar-initial" aria-hidden="true">{{ mb_substr((string) ($item['name'] ?? ''), 0, 1) }}</span>
                        @endif
                        <span>
                            <span class="fb-quote-name">{{ $item['name'] ?? '' }}</span>
                            @if (filled($item['role'] ?? null))
                                <span class="fb-quote-role">{{ $item['role'] }}</span>
                            @endif
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
@endif
