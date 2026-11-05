<?php

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Helper\VideoHelper;

// The same cases as tests/js/VideoHelper.test.ts: the two faces agree.
class VideoHelperTest extends TestCase
{
    public static function addressProvider(): array
    {
        return [
            'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
            'youtube watch, more' => ['https://youtube.com/watch?feature=share&v=dQw4w9WgXcQ&t=3', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
            'youtube short link' => ['https://youtu.be/dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
            'youtube shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
            'vimeo' => ['https://vimeo.com/76979871', 'https://player.vimeo.com/video/76979871'],
            'a file' => ['https://example.com/clip.mp4', null],
        ];
    }

    #[DataProvider('addressProvider')]
    public function testWhereAVideoPlays(string $src, ?string $expected): void
    {
        $this->assertSame($expected, VideoHelper::embedUrl($src));
    }
}
