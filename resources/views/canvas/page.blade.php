@php
    use Ferga\FilamentBuilder\Support\StyleTokens;
    use Ferga\FilamentBuilder\Blocks\SectionBlock;

    $builder = app(\Ferga\FilamentBuilder\FergaBuilder::class);
    $locales = $this->getLocales();
    $locale = $this->activeLocale;
    $blocks = $this->getBlocks();
    $context = $this->getRenderContext();
    $selectedBlock = $this->getSelectedBlock();
    $selectedType = $selectedBlock ? $this->getBlockType($selectedBlock['type'] ?? null) : null;
    $chromeDir = $builder->direction(app()->getLocale());
    $resolved = $builder->resolve($this->documents, $locale);
    $groups = $builder->registry()->grouped();
    $templateRegistry = $builder->templates();
    $templates = $templateRegistry->all();
    $backUrl = $this->getBackUrl();
@endphp

<x-filament-panels::page>
    <div
        class="fbc"
        dir="{{ $chromeDir }}"
        x-data="{
            dragging: null,
            paletteTab: 'blocks',
            dirty: @entangle('isDirty'),
            startNew(e, type) { this.dragging = { kind: 'new', type }; e.dataTransfer.effectAllowed = 'copy'; e.dataTransfer.setData('text/plain', type); },
            startTemplate(e, key) { this.dragging = { kind: 'template', key }; e.dataTransfer.effectAllowed = 'copy'; e.dataTransfer.setData('text/plain', key); },
            startMove(e, path) { e.stopPropagation(); this.dragging = { kind: 'move', path }; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', path); },
            end() { this.dragging = null; this.$root.querySelectorAll('.is-over').forEach((el) => el.classList.remove('is-over')); },
            over(e) { if (! this.dragging) return; e.preventDefault(); e.currentTarget.classList.add('is-over'); },
            leave(e) { e.currentTarget.classList.remove('is-over'); },
            drop(e, container, index) {
                e.preventDefault(); e.stopPropagation();
                const d = this.dragging; this.end();
                if (! d) return;
                if (d.kind === 'new') $wire.insertBlock(d.type, container, index);
                else if (d.kind === 'template') $wire.insertTemplate(d.key, container, index);
                else $wire.moveBlock(d.path, container, index);
            },
            keys(e) {
                const typing = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || e.target.isContentEditable || e.target.closest('.fi-modal');
                const mod = e.metaKey || e.ctrlKey;
                if (mod && e.key.toLowerCase() === 's') { e.preventDefault(); $wire.save(); return; }
                if (typing) return;
                if (mod && e.key.toLowerCase() === 'z') { e.preventDefault(); e.shiftKey ? $wire.redo() : $wire.undo(); return; }
                if (mod && e.key.toLowerCase() === 'y') { e.preventDefault(); $wire.redo(); return; }
                if ((e.key === 'Delete' || e.key === 'Backspace') && $wire.selected !== null) { e.preventDefault(); $wire.removeBlock($wire.selected); }
                if (e.key === 'Escape') { $wire.selectBlock(null); }
            },
        }"
        x-on:keydown.window="keys($event)"
        x-on:beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = ''; }"
    >
        {{-- Top bar --}}
        <header class="fbc-topbar">
            <div class="fbc-topbar-start">
                @if ($backUrl)
                    <a href="{{ $backUrl }}" class="fbc-btn fbc-btn-ghost" title="{{ __('ferga-builder::builder.canvas.back') }}">
                        <span class="fbc-flip">←</span> {{ __('ferga-builder::builder.canvas.back') }}
                    </a>
                @endif
                <span class="fbc-title">{{ $this->getRecordTitle() }}</span>
            </div>

            <nav class="fbc-locales" aria-label="{{ __('ferga-builder::builder.form.languages') }}">
                @foreach ($locales as $code => $label)
                    <button
                        type="button"
                        wire:click="switchLocale(@js($code))"
                        @class(['fbc-locale', 'is-active' => $code === $locale, 'is-empty' => empty($this->documents[$code])])
                        lang="{{ $builder->htmlLang($code) }}"
                        dir="{{ $builder->direction($code) }}"
                        title="{{ empty($this->documents[$code]) ? __('ferga-builder::builder.canvas.locale_empty') : '' }}"
                    >
                        {{ $label }}
                        @if ($builder->isRtl($code))<span class="fbc-badge">RTL</span>@endif
                    </button>
                @endforeach
                {{ $this->copyLayoutAction }}
            </nav>

            <div class="fbc-topbar-end">
                <div class="fbc-segmented" role="group" aria-label="{{ __('ferga-builder::builder.canvas.device') }}">
                    @foreach (['desktop' => \Filament\Support\Icons\Heroicon::OutlinedComputerDesktop, 'tablet' => \Filament\Support\Icons\Heroicon::OutlinedDeviceTablet, 'mobile' => \Filament\Support\Icons\Heroicon::OutlinedDevicePhoneMobile] as $device => $deviceIcon)
                        <button type="button" wire:click="setDevice('{{ $device }}')" @class(['is-active' => $this->device === $device]) title="{{ __('ferga-builder::builder.canvas.devices.'.$device) }}" aria-label="{{ __('ferga-builder::builder.canvas.devices.'.$device) }}">
                            <x-filament::icon :icon="$deviceIcon" class="fbc-btn-icon" />
                        </button>
                    @endforeach
                </div>
                <button type="button" class="fbc-btn fbc-btn-ghost" wire:click="undo" @disabled(! $this->canUndo()) title="{{ __('ferga-builder::builder.actions.undo') }} (Ctrl+Z)" aria-label="{{ __('ferga-builder::builder.actions.undo') }}">
                    <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedArrowUturnLeft" class="fbc-btn-icon fbc-flip" />
                </button>
                <button type="button" class="fbc-btn fbc-btn-ghost" wire:click="redo" @disabled(! $this->canRedo()) title="{{ __('ferga-builder::builder.actions.redo') }} (Ctrl+Shift+Z)" aria-label="{{ __('ferga-builder::builder.actions.redo') }}">
                    <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedArrowUturnRight" class="fbc-btn-icon fbc-flip" />
                </button>
                <span class="fbc-status" x-show="dirty" x-cloak>{{ __('ferga-builder::builder.canvas.unsaved') }}</span>
                <button type="button" class="fbc-btn fbc-btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                    {{ __('ferga-builder::builder.actions.save') }}
                </button>
            </div>
        </header>

        <div class="fbc-body">
            {{-- Block palette --}}
            <aside class="fbc-palette">
                @if (count($templates))
                    <div class="fbc-tabs" role="tablist">
                        <button type="button" role="tab" :aria-selected="paletteTab === 'blocks'" :class="{ 'is-active': paletteTab === 'blocks' }" x-on:click="paletteTab = 'blocks'">{{ __('ferga-builder::builder.canvas.blocks') }}</button>
                        <button type="button" role="tab" :aria-selected="paletteTab === 'templates'" :class="{ 'is-active': paletteTab === 'templates' }" x-on:click="paletteTab = 'templates'">{{ __('ferga-builder::builder.canvas.templates') }}</button>
                    </div>
                @else
                    <p class="fbc-panel-title">{{ __('ferga-builder::builder.canvas.blocks') }}</p>
                @endif

                <div x-show="paletteTab === 'templates'" x-cloak>
                    <p class="fbc-hint">{{ __('ferga-builder::builder.canvas.templates_hint') }}</p>
                    @foreach (['sections' => $templateRegistry->sections(), 'pages' => $templateRegistry->pages()] as $templateGroup => $groupTemplates)
                        @if (count($groupTemplates))
                            <p class="fbc-group-title">{{ __('ferga-builder::templates.groups.'.$templateGroup) }}</p>
                            <div class="fbc-template-list">
                                @foreach ($groupTemplates as $templateKey => $template)
                                    <button
                                        type="button"
                                        class="fbc-template"
                                        draggable="true"
                                        x-on:dragstart="startTemplate($event, @js($templateKey))"
                                        x-on:dragend="end()"
                                        wire:click="insertTemplate(@js($templateKey))"
                                    >
                                        <x-filament::icon :icon="$template->getIcon()" class="fbc-template-icon" />
                                        <span class="fbc-template-text">
                                            <span class="fbc-template-name">{{ $template->getLabel() }}</span>
                                            @if ($template->getDescription())
                                                <span class="fbc-template-description">{{ $template->getDescription() }}</span>
                                            @endif
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>

                <div x-show="paletteTab === 'blocks'">
                <p class="fbc-hint">{{ __('ferga-builder::builder.canvas.palette_hint') }}</p>
                @foreach ($groups as $group => $types)
                    <p class="fbc-group-title">{{ __('ferga-builder::builder.groups.'.$group) }}</p>
                    <div class="fbc-palette-grid">
                        @foreach ($types as $typeName => $type)
                            <button
                                type="button"
                                class="fbc-palette-item"
                                draggable="true"
                                x-on:dragstart="startNew($event, @js($typeName))"
                                x-on:dragend="end()"
                                wire:click="insertBlock(@js($typeName))"
                            >
                                <x-filament::icon :icon="$type->icon()" class="fbc-palette-icon" />
                                <span>{{ $type->label() }}</span>
                            </button>
                        @endforeach
                    </div>
                @endforeach
                </div>
            </aside>

            {{-- Canvas --}}
            <main class="fbc-scroll" x-on:click="$wire.selectBlock(null)">
                @if (empty($blocks) && $resolved['fallback'])
                    <div class="fbc-notice" dir="{{ $chromeDir }}">
                        {{ __('ferga-builder::builder.canvas.fallback_notice', ['language' => $locales[$locale] ?? $locale, 'fallback' => $locales[$resolved['locale']] ?? $resolved['locale']]) }}
                        <button type="button" class="fbc-link" wire:click="copyLayoutFrom(@js($resolved['locale']))">{{ __('ferga-builder::builder.canvas.copy_from_language', ['language' => $locales[$resolved['locale']] ?? $resolved['locale']]) }}</button>
                    </div>
                @endif

                <div
                    class="fbc-stage fbc-device-{{ $this->device }}"
                    :class="{ 'is-dragging': dragging }"
                    x-on:click.capture="if ($event.target.closest('a')) $event.preventDefault()"
                >
                    <div class="fb-content" dir="{{ $context->direction() }}" lang="{{ $context->lang() }}" data-locale="{{ $locale }}">
                        @include('ferga-builder::canvas.container', ['blocks' => $blocks, 'container' => '', 'context' => $context, 'chromeDir' => $chromeDir])
                    </div>
                </div>
            </main>

            {{-- Inspector --}}
            <aside class="fbc-inspector">
                @if ($selectedBlock && $selectedType)
                    <p class="fbc-panel-title">{{ $selectedType->label() }}</p>
                    <button type="button" class="fbc-btn fbc-btn-primary fbc-btn-block" wire:click="mountAction('editBlock', { path: @js($this->selected) })">
                        {{ __('ferga-builder::builder.actions.edit_content') }}
                    </button>

                    @if (($selectedBlock['type'] ?? null) === 'section')
                        <p class="fbc-group-title">{{ __('ferga-builder::builder.fields.layout') }}</p>
                        <div class="fbc-layouts">
                            @foreach (SectionBlock::LAYOUTS as $layout => $widths)
                                <button type="button" wire:click="setSectionLayout(@js($this->selected), '{{ $layout }}')" @class(['fbc-layout', 'is-active' => ($selectedBlock['data']['layout'] ?? '') === $layout]) title="{{ str_replace('-', ' : ', $layout) }}">
                                    @foreach ($widths as $w)<span style="flex: {{ $w }}"></span>@endforeach
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <p class="fbc-group-title">{{ __('ferga-builder::builder.style.title') }}</p>
                    @php $style = StyleTokens::normalize($selectedBlock['data']['style'] ?? null); @endphp
                    @foreach (StyleTokens::definitions() as $token => $definition)
                        <label class="fbc-field">
                            <span>{{ __('ferga-builder::builder.style.'.$token) }}</span>
                            <select wire:change="setStyle(@js($this->selected), '{{ $token }}', $event.target.value)">
                                @foreach (StyleTokens::options($token) as $value => $label)
                                    <option value="{{ $value }}" @selected($style[$token] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endforeach
                @else
                    <p class="fbc-panel-title">{{ __('ferga-builder::builder.canvas.inspector') }}</p>
                    <p class="fbc-hint">{{ __('ferga-builder::builder.canvas.select_hint') }}</p>
                @endif

                <p class="fbc-group-title">{{ __('ferga-builder::builder.canvas.outline') }}</p>
                <ol class="fbc-outline">
                    @forelse ($this->getOutline() as $row)
                        <li>
                            <button type="button" wire:click="selectBlock(@js($row['path']))" @class(['is-active' => $this->selected === $row['path'], 'is-unknown' => ! $row['known']]) style="padding-inline-start: {{ 0.5 + $row['depth'] * 1 }}rem">
                                {{ \Illuminate\Support\Str::limit($row['label'], 40) }}
                            </button>
                        </li>
                    @empty
                        <li class="fbc-hint">{{ __('ferga-builder::builder.canvas.empty') }}</li>
                    @endforelse
                </ol>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
