<?php

namespace Wexample\SymfonyDesignSystem\Service;

use JsonException;
use Wexample\SymfonyDesignSystem\Class\ElementInventory;
use Wexample\SymfonyDesignSystem\Class\ElementSource;

/**
 * The registry files: what each bundle holds, written down.
 *
 * The scan can answer the same question at any moment, but only inside php. A
 * file can be read by the build, by the node side through the published asset
 * root, by another language, by an agent — so the registry is the artefact and
 * the scan is only how it is produced. Whoever consumes it never runs the walk.
 *
 * One file per bundle, inside that bundle: a package ships the registry of what
 * *it* holds, and nothing writes into a neighbour. The picture of a whole
 * application is the union of the files, made by whoever wants it.
 *
 * A file carries no timestamp: a regeneration that found nothing new must leave
 * no diff, otherwise the registry stops saying anything by saying something
 * every time.
 */
class ElementRegistryService
{
    /**
     * The shape of the file, not the version of the bundle. It changes when a
     * reader would have to change with it, which is what a consumer pins.
     */
    final public const int VERSION = 1;

    /**
     * Under the published asset root, so that the node side reaches it as
     * `@wexample/<package>/data/elements.json` with no extra wiring.
     *
     * The directory is named apart because the scan has to know it holds what it
     * writes and not what it reads.
     */
    final public const string DIRECTORY = 'data';

    final public const string FILE_NAME = 'elements.json';

    final public const string RELATIVE_PATH = self::DIRECTORY . '/' . self::FILE_NAME;

    public function __construct(
        private readonly ElementScannerService $scannerService,
    ) {
    }

    /**
     * @return ElementSource[]
     */
    public function getSources(): array
    {
        return $this->scannerService->getSources();
    }

    public function getPath(ElementSource $source): string
    {
        return $source->path . self::RELATIVE_PATH;
    }

    public function exists(ElementSource $source): bool
    {
        return is_file($this->getPath($source));
    }

    /**
     * @throws JsonException
     */
    public function load(ElementSource $source): ?ElementInventory
    {
        if (! $this->exists($source)) {
            return null;
        }

        return ElementInventory::fromArray(
            json_decode(
                file_get_contents($this->getPath($source)),
                true,
                512,
                JSON_THROW_ON_ERROR
            )
        );
    }

    /**
     * Every registry on disk, merged.
     *
     * A source with no file is left out rather than guessed at, and is reported
     * by `findMissing()` — a page showing the union has to be able to say which
     * bundle it is missing, not just show fewer rows than it should.
     *
     * @throws JsonException
     */
    public function loadAll(): ElementInventory
    {
        $merged = new ElementInventory();

        foreach ($this->scannerService->getSources() as $source) {
            $inventory = $this->load($source);

            if ($inventory !== null) {
                $merged->merge($inventory);
            }
        }

        return $merged;
    }

    /**
     * @return ElementSource[] the sources whose registry has never been written
     */
    public function findMissing(): array
    {
        return array_values(
            array_filter(
                $this->scannerService->getSources(),
                fn (ElementSource $source): bool => ! $this->exists($source)
            )
        );
    }

    /**
     * @return bool whether the file changed, which is what tells a check run
     *              that the registry was out of date
     *
     * @throws JsonException
     */
    public function write(
        ElementSource $source,
        ElementInventory $inventory
    ): bool {
        $path = $this->getPath($source);
        $contents = $this->render($inventory);

        if ($this->exists($source) && file_get_contents($path) === $contents) {
            return false;
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0o775, true);
        }

        file_put_contents($path, $contents);

        return true;
    }

    /**
     * @throws JsonException
     */
    public function isUpToDate(
        ElementSource $source,
        ElementInventory $inventory
    ): bool {
        return $this->exists($source)
            && file_get_contents($this->getPath($source)) === $this->render($inventory);
    }

    /**
     * @throws JsonException
     */
    private function render(ElementInventory $inventory): string
    {
        return json_encode(
            ['version' => self::VERSION] + $inventory->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ) . PHP_EOL;
    }
}
