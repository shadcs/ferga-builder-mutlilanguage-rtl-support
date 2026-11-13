<div class="fb-section fb-gap-{{ in_array($data['gap'] ?? 'md', ['none', 'sm', 'md', 'lg'], true) ? ($data['gap'] ?? 'md') : 'md' }} fb-valign-{{ in_array($data['valign'] ?? 'start', ['start', 'center', 'end'], true) ? ($data['valign'] ?? 'start') : 'start' }}" style="--fb-columns: {{ $template }}">
    @foreach ($columns as $c => $column)
        @if ($context->editing)
            @include('ferga-builder::canvas.container', [
                'blocks' => (array) ($column['blocks'] ?? []),
                'container' => $context->path.'.'.$c,
                'context' => $context,
                'class' => 'fb-column',
            ])
        @else
            <div class="fb-column">
                {{ $renderer->renderList((array) ($column['blocks'] ?? []), $context->nested($context->path.'.'.$c), $context->path.'.'.$c) }}
            </div>
        @endif
    @endforeach
</div>
