<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Helper\VideoHelper;

/**
 * Where a video plays (`video_embed_url(src)`): a host's player for a host's
 * page, null for a file the browser plays — what the video component reads.
 */
class VideoExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('video_embed_url', VideoHelper::embedUrl(...)),
        ];
    }
}
