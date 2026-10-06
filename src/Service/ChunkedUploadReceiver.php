<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * A file received in pieces, in order: each one appended to what came before
 * it, and the whole moved into its directory once the last has come. The
 * pieces wait in the system's temporary directory, so a file given up halfway
 * leaves nothing in the place it was meant for.
 */
final class ChunkedUploadReceiver
{
    /**
     * The size of the pieces a browser should send: under the smaller of the
     * two limits PHP puts on one request, with room for the rest of the form.
     */
    public function chunkSize(): int
    {
        $limit = min(
            $this->iniBytes((string) ini_get('upload_max_filesize')) ?: PHP_INT_MAX,
            $this->iniBytes((string) ini_get('post_max_size')) ?: PHP_INT_MAX,
        );

        return max(256 * 1024, min(8 * 1024 * 1024, (int) ($limit * 0.8)));
    }

    /**
     * Takes one piece. Answers the path the file was stored at once it is
     * whole, null while pieces are still to come.
     *
     * @throws \InvalidArgumentException a piece out of place, a name that is no file name
     */
    public function receive(
        string $directory,
        string $uploadId,
        string $fileName,
        int $offset,
        int $total,
        UploadedFile $chunk,
    ): ?string {
        $name = $this->fileName($fileName);

        if (! preg_match('/^[A-Za-z0-9_-]{1,80}$/', $uploadId)) {
            throw new \InvalidArgumentException('This upload has no usable id.');
        }

        $partial = $this->temporaryDirectory($directory).'/'.$uploadId.'.part';
        $received = 0 === $offset ? 0 : (is_file($partial) ? (int) filesize($partial) : 0);

        // A piece sent again after a lost answer is welcome; one from a gap is not.
        if ($offset !== $received) {
            throw new \InvalidArgumentException(sprintf('Expected the piece at %d, got %d.', $received, $offset));
        }

        file_put_contents($partial, (string) file_get_contents($chunk->getPathname()), 0 === $offset ? 0 : FILE_APPEND);

        clearstatcache(true, $partial);

        if ((int) filesize($partial) < $total) {
            return null;
        }

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException(sprintf('The directory "%s" cannot be created.', $directory));
        }

        $target = $this->freePath($directory, $name);
        rename($partial, $target);

        return $target;
    }

    // The name alone, whatever path the browser sent with it.
    private function fileName(string $fileName): string
    {
        $name = trim(basename(str_replace('\\', '/', $fileName)));

        if ('' === $name || '.' === $name || '..' === $name) {
            throw new \InvalidArgumentException('This is no file name.');
        }

        return $name;
    }

    // A file already there keeps its name: the new one takes « name (2).ext ».
    private function freePath(string $directory, string $name): string
    {
        $path = $directory.'/'.$name;
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $base = '' === $extension ? $name : substr($name, 0, -strlen($extension) - 1);

        for ($index = 2; file_exists($path); ++$index) {
            $path = $directory.'/'.$base.' ('.$index.')'.('' === $extension ? '' : '.'.$extension);
        }

        return $path;
    }

    private function temporaryDirectory(string $directory): string
    {
        $path = sys_get_temp_dir().'/wexample-uploads/'.sha1($directory);

        if (! is_dir($path)) {
            mkdir($path, 0775, true);
        }

        return $path;
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);
        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
