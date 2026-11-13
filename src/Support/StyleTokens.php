<?php

namespace Ferga\FilamentBuilder\Support;

/**
 * Design tokens a block can use (stored in data.style). Rendered as classes
 * (fb-space-lg, fb-bg-muted, …) themed by CSS variables.
 */
class StyleTokens
{
    /** @return array<string, array{default: string, options: array<int, string>}> */
    public static function definitions(): array
    {
        return [
            'spacing' => ['default' => 'md', 'options' => ['none', 'sm', 'md', 'lg', 'xl']],
            'width' => ['default' => 'normal', 'options' => ['narrow', 'normal', 'wide', 'full']],
            'align' => ['default' => 'start', 'options' => ['start', 'center', 'end']],
            'background' => ['default' => 'none', 'options' => ['none', 'muted', 'primary', 'dark']],
        ];
    }

    /** @return array<string, string> option => translated label */
    public static function options(string $token): array
    {
        $options = [];

        foreach (static::definitions()[$token]['options'] ?? [] as $option) {
            $options[$option] = __("ferga-builder::builder.style.{$token}_options.{$option}");
        }

        return $options;
    }

    /** Valid tokens only, defaults filled in. */
    public static function normalize(?array $style): array
    {
        $out = [];

        foreach (static::definitions() as $token => $definition) {
            $value = $style[$token] ?? null;
            $out[$token] = in_array($value, $definition['options'], true) ? $value : $definition['default'];
        }

        return $out;
    }

    public static function isValid(string $token, mixed $value): bool
    {
        return in_array($value, static::definitions()[$token]['options'] ?? [], true);
    }

    /** @return array<int, string> */
    public static function classes(?array $style): array
    {
        $classes = [];

        foreach (static::normalize($style) as $token => $value) {
            $classes[] = match ($token) {
                'spacing' => "fb-space-{$value}",
                'width' => "fb-w-{$value}",
                'align' => "fb-align-{$value}",
                'background' => "fb-bg-{$value}",
            };
        }

        return $classes;
    }
}
