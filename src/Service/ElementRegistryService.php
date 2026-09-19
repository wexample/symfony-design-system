<?php

namespace Wexample\SymfonyDesignSystem\Service;

use JsonException;
use Wexample\SymfonyDesignSystem\Class\ElementInventory;

/**
 * The registry file: what the design system holds, written down.
 *
 * The scan can answer the same question at any moment, but only inside php. A
 * file can be read by the build, by the node side through the published asset
 * root, by another language, by an agent — so the registry is the artefact and
 * the scan is only how it is produced. Whoever consumes it never runs the walk.
 *
 * It carries no timestamp: a regeneration that found nothing new must leave no
 * diff, otherwise the file stops saying anything by saying something every time.
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
     * `@wexample/symfony-design-system/data/elements.json` with no extra wiring.
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

    public function getPath(): string
    {
        return $this->scannerService->getAssetsPath() . self::RELATIVE_PATH;
    }

    public function exists(): bool
    {
        return is_file($this->getPath());
    }

    /**
     * @throws JsonException
     */
    public function load(): ?ElementInventory
    {
        if (! $this->exists()) {
            return null;
        }

        return ElementInventory::fromArray(
            json_decode(
                file_get_contents($this->getPath()),
                true,
                512,
                JSON_THROW_ON_ERROR
            )
        );
    }

    /**
     * @return bool whether the file changed, which is what tells a check run
     *              that the registry was out of date
     *
     * @throws JsonException
     */
    public function write(ElementInventory $inventory): bool
    {
        $path = $this->getPath();
        $contents = $this->render($inventory);

        if ($this->exists() && file_get_contents($path) === $contents) {
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
    public function isUpToDate(ElementInventory $inventory): bool
    {
        return $this->exists()
            && file_get_contents($this->getPath()) === $this->render($inventory);
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
