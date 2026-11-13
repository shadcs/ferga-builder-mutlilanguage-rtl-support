<?php

namespace Ferga\FilamentBuilder\Support;

class Video
{
    /** YouTube/Vimeo page link → privacy-friendly embed URL (null when unsupported). */
    public static function embedUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        if (preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([\w-]{11})~', $url, $m)) {
            return "https://www.youtube-nocookie.com/embed/{$m[1]}";
        }

        if (preg_match('~vimeo\.com/(?:video/|channels/[\w-]+/|groups/[\w-]+/videos/)?(\d+)~', $url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}?dnt=1";
        }

        return null;
    }
}
