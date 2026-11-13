<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div {{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fb-form']) }}>
        {{ $getChildSchema() }}
    </div>
</x-dynamic-component>
