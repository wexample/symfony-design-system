<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\Finder\Finder;
use Wexample\SymfonyDesignSystem\Class\ElementSource;
use Wexample\SymfonyDesignSystem\Class\ShapeEntry;

/**
 * Walks a bundle's shapes and reads what each one says about itself.
 *
 * A shape is a stylesheet and nothing else, so there is nothing to reconcile
 * here and no declaration to put beside it: the file is the shape, its name is
 * the class it carries, and the comment at its head is what it is for. Should
 * one ever gain a renderer, it stops being a shape and moves under
 * `components/`, where the element scan takes it over — the move is the only
 * promotion there is, and it is why neither scan needs to arbitrate.
 */
class ShapeScannerService
{
    /**
     * Where shapes live under a bundle's assets root, beside the mixins and the
     * palette — the part of the tree that is not a component.
     */
    final public const string DIRECTORY = 'css/shapes';

    /**
     * Sass partials, which is what a shape is: never built on its own, only
     * pulled in by whoever writes the markup it styles.
     */
    private const string FILE_PATTERN = '_*.scss';

    /**
     * @return ShapeEntry[] keyed by id, so that inventories merge the way the
     *                      element entries do
     */
    public function scan(ElementSource $source): array
    {
        $root = $source->path . self::DIRECTORY;

        if (! is_dir($root)) {
            return [];
        }

        $shapes = [];

        foreach ((new Finder())->files()->name(self::FILE_PATTERN)->in($root) as $file) {
            $key = $this->readKey($file->getRelativePathname());

            $entry = new ShapeEntry(
                $key,
                $source->alias,
                $source->qualify(self::DIRECTORY . '/' . $file->getRelativePathname()),
                $this->readDescription($file->getContents()),
            );

            $shapes[$entry->getId()] = $entry;
        }

        ksort($shapes);

        return $shapes;
    }

    /**
     * The name of a shape is its path without the partial underscore:
     * `form/_select.scss` is `form/select`.
     */
    private function readKey(string $relativePathname): string
    {
        $directory = dirname($relativePathname);
        $name = substr(basename($relativePathname, '.scss'), 1);

        return $directory === '.' ? $name : $directory . '/' . $name;
    }

    /**
     * The comment block opening the file, down to the first line that is not
     * one. A shape with nothing to say shows an empty cell, which is the
     * invitation to write it.
     */
    private function readDescription(string $contents): ?string
    {
        $lines = [];

        foreach (preg_split('/\R/', $contents) as $line) {
            $line = trim($line);

            if (! str_starts_with($line, '//')) {
                break;
            }

            $lines[] = trim(substr($line, 2));
        }

        $description = trim(implode(' ', $lines));

        return $description === '' ? null : $description;
    }
}
