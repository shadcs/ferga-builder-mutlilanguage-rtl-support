{{-- Editable list of blocks. @param array $blocks, string $container, RenderContext $context --}}
@php
    $renderer = app(\Ferga\FilamentBuilder\FergaBuilder::class)->renderer();
    $registry = app(\Ferga\FilamentBuilder\FergaBuilder::class)->registry();
    $selected = $context->editor['selected'] ?? null;
    $chromeDir = $context->editor['chromeDir'] ?? 'ltr';
@endphp
<div class="fbc-container {{ $class ?? '' }} @if (empty($blocks)) is-empty @endif" data-container="{{ $container }}">
    <div class="fbc-slot" x-on:dragover="over($event)" x-on:dragleave="leave($event)" x-on:drop="drop($event, @js($container), 0)"></div>

    @foreach (array_values($blocks) as $i => $block)
        @php
            $path = $container === '' ? (string) $i : $container.'.'.$i;
            $type = $registry->get($block['type'] ?? null);
        @endphp
        <div
            wire:key="fbc-{{ $block['id'] ?? $path }}"
            @class(['fbc-node', 'is-selected' => $selected === $path, 'is-unknown' => ! $type, 'is-section' => ($block['type'] ?? null) === 'section'])
            data-path="{{ $path }}"
            draggable="true"
            x-on:dragstart="startMove($event, @js($path))"
            x-on:dragend="end()"
            x-on:click.stop="$wire.selectBlock(@js($path))"
            x-on:dblclick.stop="$wire.mountAction('editBlock', { path: @js($path) })"
        >
            <div class="fbc-node-bar" dir="{{ $chromeDir }}">
                <span class="fbc-node-handle" title="{{ __('ferga-builder::builder.canvas.drag') }}">⠿</span>
                <span class="fbc-node-label">{{ $type?->label() ?? ($block['type'] ?? '?') }}</span>
                <span class="fbc-node-tools">
                    <button type="button" class="fbc-icon-btn" x-on:click.stop="$wire.mountAction('editBlock', { path: @js($path) })" title="{{ __('ferga-builder::builder.actions.edit') }}">✎</button>
                    <button type="button" class="fbc-icon-btn" x-on:click.stop="$wire.moveBlockBy(@js($path), -1)" title="{{ __('ferga-builder::builder.actions.move_up') }}">↑</button>
                    <button type="button" class="fbc-icon-btn" x-on:click.stop="$wire.moveBlockBy(@js($path), 1)" title="{{ __('ferga-builder::builder.actions.move_down') }}">↓</button>
                    <button type="button" class="fbc-icon-btn" x-on:click.stop="$wire.duplicateBlock(@js($path))" title="{{ __('ferga-builder::builder.actions.duplicate') }}">⧉</button>
                    <button type="button" class="fbc-icon-btn is-danger" x-on:click.stop="$wire.removeBlock(@js($path))" title="{{ __('ferga-builder::builder.actions.delete') }}">✕</button>
                </span>
            </div>

            @if ($type)
                {!! $renderer->renderBlock($block, $context->at($path)) !!}
            @else
                <div class="fb-placeholder">{{ __('ferga-builder::builder.canvas.unknown_block', ['type' => $block['type'] ?? '?']) }}</div>
            @endif
        </div>

        <div class="fbc-slot" x-on:dragover="over($event)" x-on:dragleave="leave($event)" x-on:drop="drop($event, @js($container), {{ $i + 1 }})"></div>
    @endforeach

    @if (empty($blocks))
        <div class="fbc-empty" x-on:dragover="over($event)" x-on:dragleave="leave($event)" x-on:drop="drop($event, @js($container), 0)">
            {{ __('ferga-builder::builder.canvas.drop_here') }}
        </div>
    @endif
</div>
