<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Wexample\Helpers\Helper\TextHelper;
use Wexample\SymfonyDesignSystem\Class\ElementInventory;
use Wexample\SymfonyDesignSystem\Enum\ElementFormat;
use Wexample\SymfonyDesignSystem\Twig\InventoryExtension;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;

/**
 * Walks the bundle's assets and answers what elements are there, in what formats.
 *
 * It is a stopgap with a purpose: the design system has no place saying that an
 * element exists — the name is a file name, the options are arguments buried in
 * a twig extension, the documentation is a demo page — so the only honest way to
 * count is to look. What the scan produces is meant to be read once and turned
 * into a declaration; until then it is what the inventory page shows, and it
 * stays true as files move, which a hand-written list would not.
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
     * The twig extensions a function must come from to be counted. The loader
     * registers `component()` and `vue()`, which are how elements are drawn and
     * not elements themselves.
     */
    private const string TWIG_NAMESPACE = 'Wexample\\SymfonyDesignSystem\\Twig\\';

    public function scan(Environment $twig): ElementInventory
    {
        $inventory = new ElementInventory($this->findUnscannedPaths());

        foreach (ElementFormat::fileBased() as $format) {
            $this->scanFormat($inventory, $format);
        }

        // Functions come last: a function is attached to the element it draws,
        // and that element is only known once the files have been walked.
        $this->scanTwigFunctions($inventory, $twig);

        return $inventory;
    }

    public function getAssetsPath(): string
    {
        return realpath(
            current(WexampleSymfonyDesignSystemBundle::getLoaderFrontPaths())
        ) . '/';
    }

    private function scanFormat(
        ElementInventory $inventory,
        ElementFormat $format
    ): void {
        $directory = $this->getAssetsPath() . $format->getDirectory();

        if (! is_dir($directory)) {
            return;
        }

        $finder = (new Finder())
            ->files()
            ->in($directory)
            ->name($this->getPrimaryFilePattern($format));

        foreach ($finder as $file) {
            $relative = $format->getDirectory() . '/' . $file->getRelativePathname();
            $key = $this->buildKey($file->getRelativePathname());

            $entry = $inventory->entry($key);

            $entry->add($format, $relative);

            foreach ($this->findSiblings($file->getPath(), $file->getRelativePathname()) as $sibling) {
                $entry->add($format, $format->getDirectory() . '/' . $sibling);
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
     * Attaches every twig function of the bundle to the element it draws.
     *
     * `status_icon` names its element outright; `button_link` and `message_info`
     * do not, and are read as what they are — a way of calling `button` and
     * `message`. The longest prefix that is already an element wins, and a
     * function matching none keeps a row of its own, which is the right answer
     * for `ui_state_get`: it draws nothing.
     */
    private function scanTwigFunctions(
        ElementInventory $inventory,
        Environment $twig
    ): void {
        foreach ($twig->getExtensions() as $extension) {
            // The function handing this scan to a template is not an element,
            // and counting it would make the inventory list itself.
            if (! $this->isDesignSystemExtension($extension)
                || $extension instanceof InventoryExtension
            ) {
                continue;
            }

            foreach ($extension->getFunctions() as $function) {
                $name = $function->getName();

                $inventory
                    ->entry($this->resolveFunctionKey($inventory, $name))
                    ->add(ElementFormat::TWIG_FUNCTION, $name);
            }
        }
    }

    private function isDesignSystemExtension(ExtensionInterface $extension): bool
    {
        return str_starts_with($extension::class, self::TWIG_NAMESPACE);
    }

    private function resolveFunctionKey(
        ElementInventory $inventory,
        string $functionName
    ): string {
        $segments = explode('_', $functionName);

        while ($segments !== []) {
            $key = implode('-', $segments);

            if ($inventory->hasEntry($key)) {
                return $key;
            }

            array_pop($segments);
        }

        return TextHelper::toKebab($functionName);
    }

    /**
     * What the scan does not look at, computed rather than listed, so that the
     * page showing the inventory cannot claim a coverage it lost.
     *
     * @return string[]
     */
    private function findUnscannedPaths(): array
    {
        $scanned = array_map(
            static fn (ElementFormat $format): string => $format->getDirectory(),
            ElementFormat::fileBased()
        );

        $paths = [];

        foreach ([null, 'css'] as $parent) {
            $directory = $this->getAssetsPath() . $parent;

            if (! is_dir($directory)) {
                continue;
            }

            foreach ((new Finder())->directories()->depth(0)->in($directory) as $child) {
                $path = ($parent ? $parent . '/' : '') . $child->getFilename();

                if (! in_array($path, $scanned, true) && $path !== 'css') {
                    $paths[] = $path;
                }
            }
        }

        sort($paths);

        return $paths;
    }
}
