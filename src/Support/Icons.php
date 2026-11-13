<?php

namespace Ferga\FilamentBuilder\Support;

use Illuminate\Support\Str;
use Throwable;

class Icons
{
    /** Curated Heroicons for the highlights block (extend via Icons::$icons). */
    public static array $icons = [
        'bolt', 'shield-check', 'heart', 'star', 'sparkles', 'check-circle', 'clock', 'globe-alt',
        'wrench-screwdriver', 'cog-6-tooth', 'cpu-chip', 'light-bulb', 'rocket-launch', 'trophy',
        'users', 'user-group', 'chat-bubble-left-right', 'phone', 'envelope', 'map-pin', 'building-office-2',
        'home', 'truck', 'currency-dollar', 'banknotes', 'chart-bar', 'arrow-trending-up', 'lock-closed',
        'key', 'fire', 'beaker', 'academic-cap', 'calendar-days', 'document-text', 'photo', 'play-circle',
        'hand-thumb-up', 'face-smile', 'sun', 'arrow-right', 'arrow-up-right', 'chevron-right',
    ];

    /** Icons that point in a reading direction and must mirror in RTL. */
    public static array $directional = [
        'arrow-right', 'arrow-left', 'arrow-up-right', 'arrow-up-left', 'arrow-down-right', 'arrow-down-left',
        'arrow-long-right', 'arrow-long-left', 'chevron-right', 'chevron-left', 'chevron-double-right',
        'chevron-double-left', 'arrow-uturn-left', 'arrow-uturn-right', 'arrow-right-circle', 'arrow-left-circle',
        'arrow-trending-up', 'arrow-trending-down', 'chat-bubble-left-right', 'arrow-small-right', 'arrow-small-left',
    ];

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (static::$icons as $name) {
            $options["heroicon-o-{$name}"] = Str::headline($name);
        }

        return $options;
    }

    public static function isDirectional(?string $icon): bool
    {
        if (blank($icon)) {
            return false;
        }

        $name = preg_replace('/^heroicon-[osmc]-/', '', $icon);

        return in_array($name, static::$directional, true);
    }

    /** Inline SVG for a blade-icons name, or '' when unknown. */
    public static function svg(?string $icon, string $class = 'fb-icon'): string
    {
        if (blank($icon) || ! function_exists('svg')) {
            return '';
        }

        if (static::isDirectional($icon)) {
            $class .= ' fb-icon-directional';
        }

        try {
            return svg($icon, $class, ['aria-hidden' => 'true'])->toHtml();
        } catch (Throwable) {
            return '';
        }
    }
}
