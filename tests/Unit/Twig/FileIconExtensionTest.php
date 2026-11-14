<?php

declare(strict_types=1);

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Twig;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Wexample\SymfonyDesignSystem\Twig\FileIconExtension;

/**
 * What a file looks like from its name, as FileIconHelper.ts draws it: the
 * two tables must give the same answer on either side.
 */
class FileIconExtensionTest extends TestCase
{
    public static function kindsProvider(): array
    {
        return [
            'pdf' => ['report.pdf', 'pdf'],
            'uppercase extension' => ['PHOTO.JPG', 'image'],
            'svg is its own kind' => ['logo.svg', 'svg'],
            'last extension wins' => ['backup.tar.gz', 'archive'],
            'vue reads as javascript' => ['app.vue', 'javascript'],
            'twig reads as html' => ['page.html.twig', 'html'],
            'unknown extension' => ['notes.xyz', 'file'],
            'no extension' => ['Makefile', 'file'],
        ];
    }

    #[DataProvider('kindsProvider')]
    public function testTheKindComesFromTheExtension(string $name, string $kind): void
    {
        $this->assertSame($kind, (new FileIconExtension())->fileKind($name));
    }

    public function testADirectoryIsADirectoryWhateverItsName(): void
    {
        $extension = new FileIconExtension();

        $this->assertSame('directory', $extension->fileKind('archive.zip', true));
        $this->assertSame('ph:bold/folder', $extension->fileIcon('archive.zip', true));
        $this->assertSame('ph:bold/folder-open', $extension->fileIcon('archive.zip', true, true));
    }

    public function testTheIconFollowsTheKind(): void
    {
        $extension = new FileIconExtension();

        $this->assertSame('ph:bold/file-pdf', $extension->fileIcon('report.pdf'));
        $this->assertSame('ph:bold/file-code', $extension->fileIcon('composer.lock'));
        // An open file is no different from a closed one.
        $this->assertSame('ph:bold/file-pdf', $extension->fileIcon('report.pdf', false, true));
        $this->assertSame('ph:bold/file', $extension->fileIcon('notes.xyz'));
    }

    public static function bytesProvider(): array
    {
        return [
            'nothing' => [null, 1, '0 B'],
            'zero' => [0, 1, '0 B'],
            'negative' => [-5, 1, '0 B'],
            'bytes' => [512, 1, '512 B'],
            'one kilobyte, no trailing zero' => [1024, 1, '1 KB'],
            'rounded' => [1536, 1, '1.5 KB'],
            'decimals asked for' => [1_234_567, 2, '1.18 MB'],
            'gigabytes' => [3 * 1024 ** 3, 1, '3 GB'],
        ];
    }

    #[DataProvider('bytesProvider')]
    public function testASizeReadsAsBytesFormatBytesWritesIt(int|float|null $bytes, int $decimals, string $expected): void
    {
        $this->assertSame($expected, (new FileIconExtension())->fileBytes($bytes, $decimals));
    }
}
