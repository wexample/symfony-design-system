<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Wexample\Helpers\Helper\TextHelper;
use Wexample\SymfonyDesignSystem\Class\ElementInventory;
use Wexample\SymfonyDesignSystem\Class\ElementSource;
use Wexample\SymfonyDesignSystem\Enum\ElementFormat;
use Wexample\SymfonyDesignSystem\Twig\InventoryExtension;

/**
 * Walks a bundle's assets and answers what elements are there, in what formats.
 *
 * It is a stopgap with a purpose: the design system has no place saying that an
 * element exists — the name is a file name, the options are arguments buried in
 * a twig extension, the documentation is a demo page — so the only honest way to
 * count is to look. What it finds is written to that bundle's registry by
 * `ElementRegistryService`, and everything else reads the file; the walk runs
 * when a registry is regenerated, never when a page is drawn.
 *
 * The scan is meant to be read once and turned into a declaration. The day it is,
 * it keeps its use as the registry's contradictor: what a declaration claims,
 * against what is on disk.
 */
class ElementScannerService
{
    /**
     * Directory names the formats put the same element under.
     *
     * A vue twin of a partial sits in `vue/partials/`, the server-side original
     * in `partials/`: nothing but the directory differs, so the directory is
     * dropped on both sides rather than guessed at match time. Anything not
     * listed keeps its path, which is how an element existing in one format only
     * stays visible as such instead of being quietly merged into a neighbour.
     */
    private const array PATH_ALIASES = [
        'partials/' => '',
        'fields/' => '',
    ];

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

        foreach (ElementFormat::fileBased() as $format) {
            $this->scanFormat($inventory, $source, $format);
        }

        // Functions come last: a function is attached to the element it draws,
        // and that element is only known once the files have been walked.
        $this->scanTwigFunctions($inventory, $source, $twig);

        return $inventory;
    }

    private function scanFormat(
        ElementInventory $inventory,
        ElementSource $source,
        ElementFormat $format
    ): void {
        $directory = $source->path . $format->getDirectory();

        if (! is_dir($directory)) {
            return;
        }

        $finder = (new Finder())
            ->files()
            ->in($directory)
            ->name($this->getPrimaryFilePattern($format));

        foreach ($finder as $file) {
            $entry = $inventory->entry($source->alias, $this->buildKey($file->getRelativePathname()));

            $entry->add(
                $format,
                $source->qualify($format->getDirectory() . '/' . $file->getRelativePathname())
            );

            foreach ($this->findSiblings($file->getPath(), $file->getRelativePathname()) as $sibling) {
                $entry->add(
                    $format,
                    $source->qualify($format->getDirectory() . '/' . $sibling)
                );
            }
        }
    }

    /**
     * The one file whose presence means the element exists in that format. The
     * script and the stylesheet beside it are found as siblings, so a component
     * with no template is not an element with a stray script.
     */
    private function getPrimaryFilePattern(ElementFormat $format): string
    {
        return match ($format) {
            ElementFormat::SHAPE => '*.scss',
            ElementFormat::PARTIAL, ElementFormat::COMPONENT => '*.html.twig',
            ElementFormat::VUE => '*.vue',
            ElementFormat::TWIG_FUNCTION => '',
        };
    }

    /**
     * @return string[] relative pathnames of the files carrying the same stem
     */
    private function findSiblings(
        string $path,
        string $relativePathname
    ): array {
        $directory = dirname($relativePathname);
        $directory = $directory === '.' ? '' : $directory . '/';
        $stem = $this->removeKnownExtension(basename($relativePathname));

        $siblings = [];

        foreach ((new Finder())->files()->depth(0)->in($path)->name($stem . '.*') as $file) {
            $name = $file->getFilename();

            if ($name !== basename($relativePathname)) {
                $siblings[] = $directory . $name;
            }
        }

        sort($siblings);

        return $siblings;
    }

    /**
     * What names an element across the formats: its path under the format's
     * directory, in kebab case, aliases applied.
     *
     * The leading underscore of a scss partial and the `.front` of a template
     * rendered in the browser are notations of the format, not of the element,
     * so both come off — `banner.front.html.twig` is the same banner.
     */
    private function buildKey(string $relativePathname): string
    {
        $path = $this->removeKnownExtension($relativePathname);
        $path = str_replace('.front', '', $path);

        $segments = array_map(
            static fn (string $segment): string => TextHelper::toKebab(ltrim($segment, '_')),
            explode('/', $path)
        );

        $key = implode('/', $segments);

        foreach (self::PATH_ALIASES as $search => $replace) {
            $key = str_replace($search, $replace, $key);
        }

        return $key;
    }

    private function removeKnownExtension(string $fileName): string
    {
        foreach (['.html.twig', '.vue.twig', '.scss', '.vue', '.ts', '.twig'] as $extension) {
            if (str_ends_with($fileName, $extension)) {
                return substr($fileName, 0, -strlen($extension));
            }
        }

        return $fileName;
    }

    /**
     * Attaches every twig function of the source's bundle to the element it draws.
     *
     * `status_icon` names its element outright; `button_link` and `message_info`
     * do not, and are read as what they are — a way of calling `button` and
     * `message`. The longest prefix that is already an element wins, and a
     * function matching none keeps a row of its own, which is the right answer
     * for `ui_state_get`: it draws nothing.
     */
    private function scanTwigFunctions(
        ElementInventory $inventory,
        ElementSource $source,
        Environment $twig
    ): void {
        foreach ($twig->getExtensions() as $extension) {
            // The functions handing a registry to a template are not elements,
            // and counting them would make the registry list itself.
            if (! $this->belongsToSource($extension, $source)
                || $extension instanceof InventoryExtension
            ) {
                continue;
            }

            foreach ($extension->getFunctions() as $function) {
                $name = $function->getName();

                $inventory
                    ->entry($source->alias, $this->resolveFunctionKey($inventory, $source, $name))
                    ->add(ElementFormat::TWIG_FUNCTION, $name);
            }
        }
    }

    private function belongsToSource(
        ExtensionInterface $extension,
        ElementSource $source
    ): bool {
        return str_starts_with($extension::class, $source->twigNamespace);
    }

    private function resolveFunctionKey(
        ElementInventory $inventory,
        ElementSource $source,
        string $functionName
    ): string {
        $segments = explode('_', $functionName);

        while ($segments !== []) {
            $key = implode('-', $segments);

            if ($inventory->hasEntry($source->alias, $key)) {
                return $key;
            }

            array_pop($segments);
        }

        return TextHelper::toKebab($functionName);
    }

    /**
     * What the scan does not look at, computed rather than listed, so that a page
     * showing the registry cannot claim a coverage it lost.
     *
     * @return string[]
     */
    private function findUnscannedPaths(ElementSource $source): array
    {
        $scanned = array_map(
            static fn (ElementFormat $format): string => $format->getDirectory(),
            ElementFormat::fileBased()
        );

        $paths = [];

        foreach ([null, 'css'] as $parent) {
            $directory = $source->path . $parent;

            if (! is_dir($directory)) {
                continue;
            }

            foreach ((new Finder())->directories()->depth(0)->in($directory) as $child) {
                $path = ($parent ? $parent . '/' : '') . $child->getFilename();

                // The registry's directory holds what the registry writes, the
                // declarations' directory what is said about elements: neither
                // holds elements, and calling them uncovered would be true,
                // useless, and a way for each run to disagree with the last.
                if (! in_array($path, $scanned, true)
                    && $path !== 'css'
                    && $path !== ElementRegistryService::DIRECTORY
                    && $path !== ElementDeclarationService::DIRECTORY
                ) {
                    $paths[] = $source->qualify($path);
                }
            }
        }

        sort($paths);

        return $paths;
    }
}
