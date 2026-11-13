@if (filled($data['text'] ?? null))
    <figure class="fb-quote">
        <blockquote class="fb-quote-text">{!! nl2br(e($data['text'])) !!}</blockquote>
        @if (filled($data['author'] ?? null))
            <figcaption class="fb-quote-author">
                <span class="fb-quote-name">{{ $data['author'] }}</span>
                @if (filled($data['role'] ?? null))
                    <span class="fb-quote-role">{{ $data['role'] }}</span>
                @endif
            </figcaption>
        @endif
    </figure>
@endif
