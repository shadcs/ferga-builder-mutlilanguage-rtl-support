@php $level = in_array($data['level'] ?? 'h2', ['h1', 'h2', 'h3', 'h4'], true) ? ($data['level'] ?? 'h2') : 'h2'; @endphp
@if (filled($data['text'] ?? null))
    <header class="fb-heading fb-heading-{{ $level }}">
        @if (filled($data['eyebrow'] ?? null))
            <p class="fb-eyebrow">{{ $data['eyebrow'] }}</p>
        @endif
        <{{ $level }} class="fb-heading-text">{{ $data['text'] }}</{{ $level }}>
        @if (filled($data['subtitle'] ?? null))
            <p class="fb-subtitle">{{ $data['subtitle'] }}</p>
        @endif
    </header>
@endif
