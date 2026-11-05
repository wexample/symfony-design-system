<?php

namespace Wexample\SymfonyDesignSystem\Helper;

/**
 * Where a video plays, as the video component reads it: a host's page for
 * it (YouTube, Vimeo) is played by the host's own player, embedded; any
 * other address is a file the browser plays. The twin of VideoHelper.ts,
 * which the vue follows: a change to either is a change to both.
 */
class VideoHelper
{
    private const string YOUTUBE = '#^https?://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([\w-]{6,})#i';

    private const string VIMEO = '#^https?://(?:www\.|player\.)?vimeo\.com/(?:video/)?(\d+)#i';

    /**
     * The host's player for a host's page; null for a file.
     */
    public static function embedUrl(string $src): ?string
    {
        if (preg_match(self::YOUTUBE, trim($src), $match)) {
            return 'https://www.youtube-nocookie.com/embed/'.$match[1];
        }

        if (preg_match(self::VIMEO, trim($src), $match)) {
            return 'https://player.vimeo.com/video/'.$match[1];
        }

        return null;
    }
}
