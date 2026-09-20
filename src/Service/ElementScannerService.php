<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Class\ElementInventory;
use Wexample\SymfonyDesignSystem\Class\ElementSource;
use Wexample\SymfonyDesignSystem\Enum\ElementFormat;

/**
 * Walks a bundle's components and answers which renderers each one has.
 *
 * There is nothing to guess any more: a component is a directory under
 * `components/`, its name is its path, and a renderer is a file inside it named
 * after it. The scan that had to reconcile four directories by name now only
 * reads what the layout already says — which is the whole point of having moved
 * everything into one place.
 *
 * What it finds is written to that bundle's registry by `ElementRegistryService`
 * and everything else reads the file; the walk runs when a registry is
 * regenerated, never when a page is drawn. Its lasting use is as the registry's
 * contradictor: what a declaration claims, against what is on disk.
 */
class ElementScannerService
{
    /**
     * Where components live under a bundle's assets root.
     */
    final public const string DIRECTORY = 'components';

    /**
     * @var ElementSource[]
     */
    private readonly array $sources;

    /**
     * @param array[] $sources as the container collected them from the bundles
     *                         that declared themselves holders of elements
     */
    public function __construct(array $sources)
    {
        $this->sources = array_map(
            static fn (array $source): ElementSource => ElementSource::fromArray($source),
            $sources
        );
    }

    /**
     * @return ElementSource[]
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    public function getSource(string $alias): ?ElementSource
    {
        foreach ($this->sources as $source) {
            if ($source->alias === $alias) {
                return $source;
            }
        }

        return null;
    }

    /**
     * What one bundle holds. Sources are scanned apart and never merged here:
     * each one writes its own registry, and whoever wants the whole picture
     * merges the files rather than asking for a walk of everything.
     */
    public function scan(
        Environment $twig,
        ElementSource $source
    ): ElementInventory {
        $inventory = new ElementInventory($this->findUnscannedPaths($source));
        $root = $source->path . self::DIRECTORY;

        if (! is_dir($root)) {
            return $inventory;
        }

        foreach ((new Finder())->files()->in($root) as $file) {
            $key = $this->readKey($file->getRelativePathname());

            // A file not named after the directory it sits in belongs to no
            // component: it is something a component imports, and the registry
            // has nothing to say about it.
            if ($key === null) {
                continue;
            }

            $format = $this->readFormat($file->getFilename());

            if ($format === null) {
                continue;
            }

            $inventory
                ->entry($source->alias, $key)
                ->add($format, $source->qualify(self::DIRECTORY . '/' . $file->getRelativePathname()));
        }

        return $inventory;
    }

    /**
     * The component a file belongs to, which is its directory — but only when
     * the file carries that directory's name. `button/button.scss` is the style
     * of `button`; `button/mixins.scss` is a file `button` happens to keep.
     */
    private function readKey(string $relativePathname): ?string
    {
        $directory = dirname($relativePathname);

        if ($directory === '.') {
            return null;
        }

        $expected = basename($directory);
        $name = basename($relativePathname);

        return str_starts_with($name, $expected . '.') ? $directory : null;
    }

    private function readFormat(string $fileName): ?ElementFormat
    {
        foreach (ElementFormat::cases() as $format) {
            foreach ($format->getSuffixes() as $suffix) {
                if (str_ends_with($fileName, $suffix)) {
                    return $format;
                }
            }
        }

        return null;
    }

    /**
     * What the scan does not look at, computed rather than listed, so that a
     * page showing the registry cannot claim a coverage it lost.
     *
     * @return string[]
     */
    private function findUnscannedPaths(ElementSource $source): array
    {
        $paths = [];

        foreach ([null, 'css'] as $parent) {
            $directory = $source->path . $parent;

            if (! is_dir($directory)) {
                continue;
            }

            foreach ((new Finder())->directories()->depth(0)->in($directory) as $child) {
                $path = ($parent ? $parent . '/' : '') . $child->getFilename();

                // What the registry writes and what is said about components
                // are neither of them components; naming them uncovered would
                // be true, useless, and a way for each run to disagree with the
                // last.
                if (! in_array($path, [
                    self::DIRECTORY,
                    'css',
                    ElementRegistryService::DIRECTORY,
                    ElementDeclarationService::DIRECTORY,
                ], true)) {
                    $paths[] = $source->qualify($path);
                }
            }
        }

        sort($paths);

        return $paths;
    }
}
