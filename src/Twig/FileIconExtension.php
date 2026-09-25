<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * What a file looks like, from its name: `file_icon(name, isDirectory)` gives
 * the icon for its extension, `file_kind(name, isDirectory)` the kind it
 * belongs to. The twin of FileIconHelper.ts, with the same table.
 */
class FileIconExtension extends AbstractExtension
{
    private const array KINDS = [
        'pdf' => ['pdf'],
        'image' => ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'ico', 'tif', 'tiff', 'avif', 'heic'],
        'svg' => ['svg'],
        'archive' => ['zip', 'tar', 'gz', 'tgz', 'bz2', 'xz', '7z', 'rar'],
        'audio' => ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac'],
        'video' => ['mp4', 'mov', 'avi', 'mkv', 'webm'],
        'spreadsheet' => ['xls', 'xlsx', 'ods'],
        'csv' => ['csv', 'tsv'],
        'document' => ['doc', 'docx', 'odt', 'rtf'],
        'presentation' => ['ppt', 'pptx', 'odp'],
        'markdown' => ['md', 'markdown'],
        'html' => ['html', 'htm', 'twig'],
        'css' => ['css', 'scss', 'sass', 'less'],
        'javascript' => ['js', 'mjs', 'cjs', 'jsx', 'vue'],
        'typescript' => ['ts', 'tsx'],
        'python' => ['py'],
        'sql' => ['sql'],
        'code' => ['php', 'java', 'go', 'rs', 'rb', 'c', 'h', 'cpp', 'cs', 'sh', 'bash', 'json', 'yml', 'yaml', 'xml', 'toml', 'ini', 'lock'],
        'text' => ['txt', 'log', 'env'],
    ];

    private const array ICONS = [
        'pdf' => 'ph:bold/file-pdf',
        'image' => 'ph:bold/file-image',
        'svg' => 'ph:bold/file-svg',
        'archive' => 'ph:bold/file-zip',
        'audio' => 'ph:bold/file-audio',
        'video' => 'ph:bold/file-video',
        'spreadsheet' => 'ph:bold/file-xls',
        'csv' => 'ph:bold/file-csv',
        'document' => 'ph:bold/file-doc',
        'presentation' => 'ph:bold/file-ppt',
        'markdown' => 'ph:bold/file-md',
        'html' => 'ph:bold/file-html',
        'css' => 'ph:bold/file-css',
        'javascript' => 'ph:bold/file-js',
        'typescript' => 'ph:bold/file-ts',
        'python' => 'ph:bold/file-py',
        'sql' => 'ph:bold/file-sql',
        'code' => 'ph:bold/file-code',
        'text' => 'ph:bold/file-text',
    ];

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_icon', $this->fileIcon(...)),
            new TwigFunction('file_kind', $this->fileKind(...)),
        ];
    }

    public function fileKind(string $name, bool $isDirectory = false): string
    {
        if ($isDirectory) {
            return 'directory';
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        foreach (self::KINDS as $kind => $extensions) {
            if (in_array($extension, $extensions, true)) {
                return $kind;
            }
        }

        return 'file';
    }

    public function fileIcon(string $name, bool $isDirectory = false, bool $open = false): string
    {
        if ($isDirectory) {
            return $open ? 'ph:bold/folder-open' : 'ph:bold/folder';
        }

        return self::ICONS[$this->fileKind($name)] ?? 'ph:bold/file';
    }
}
