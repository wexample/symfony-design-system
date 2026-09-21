<?php

namespace Wexample\SymfonyDesignSystem\Service;

use JsonException;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Class\ElementCompilation;
use Wexample\SymfonyDesignSystem\Class\ElementInventory;
use Wexample\SymfonyDesignSystem\Class\ElementSource;
use Wexample\SymfonyDesignSystem\Enum\ElementFormat;
use Wexample\SymfonyDesignSystem\Enum\FormatStance;

/**
 * The registry files: what each bundle holds, written down for whoever reads.
 *
 * A registry is compiled from two things that must agree — the bundle's yaml
 * declarations, which say what each element is and which formats it is expected
 * in, and a scan of its assets, which says what is actually there. Where they
 * agree, the file follows; where they do not, the compilation names the point
 * and nothing is written, since a registry built from a contradiction would only
 * be read by something that trusts it.
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
    final public const int VERSION = 2;

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
        private readonly ElementDeclarationService $declarationService,
    ) {
    }

    /**
     * @return ElementSource[]
     */
    public function getSources(): array
    {
        return $this->scannerService->getSources();
    }

    /**
     * Puts a bundle's declarations beside its scan and says whether they agree.
     *
     * Four ways they can fail to, each named after the element:
     *
     * - files on disk that no declaration claims;
     * - a declaration with nothing on disk behind it;
     * - a format declared expected and not found;
     * - a format found that the declaration says is not carried — whether it
     *   was waived or left to do, the sentence has outlived what it described.
     *
     * A format neither found nor ruled on is not a failure but a decision not
     * yet made: it is counted and named, never fatal.
     */
    public function compile(
        Environment $twig,
        ElementSource $source
    ): ElementCompilation {
        $scanned = $this->scannerService->scan($twig, $source);
        $declarations = $this->declarationService->loadAll($source);
        $problems = [];
        $pending = [];
        $todo = [];

        foreach ($scanned->getEntries() as $entry) {
            if (! isset($declarations[$entry->key])) {
                $problems[] = sprintf(
                    '%s: found on disk but not declared — seed it, or delete %s.',
                    $entry->getId(),
                    implode(', ', array_merge(...array_values($entry->getOccurrences())))
                );
            }
        }

        foreach ($declarations as $key => $declaration) {
            $entry = $scanned->findEntry($source->alias, $key);

            if ($entry === null) {
                $problems[] = sprintf(
                    '%s: declared but nothing on disk carries it — delete %s.',
                    $source->alias . ':' . $key,
                    $this->declarationService->getPath($source, $key)
                );

                continue;
            }

            $entry->description = $declaration->description;

            foreach (ElementFormat::cases() as $format) {
                $found = $entry->has($format);
                $stance = $declaration->getStance($format);

                $problem = $this->compareFormat($entry->getId(), $stance, $format, $found);

                if ($problem !== null) {
                    $problems[] = $problem;
                }

                if (! $found) {
                    match ($stance) {
                        FormatStance::UNDECIDED => $pending[] = sprintf('%s: %s', $entry->getId(), $format->value),
                        FormatStance::TODO => $todo[] = sprintf(
                            '%s: %s%s',
                            $entry->getId(),
                            $format->value,
                            ($note = $declaration->getNote($format)) !== null ? ' — ' . $note : ''
                        ),
                        default => null,
                    };
                }

                $entry->setStance($format, $stance, $declaration->getNote($format));
            }
        }

        return new ElementCompilation($scanned, $problems, $pending, $todo);
    }

    /**
     * Only EXPECTED says the format is carried; the three others all say it is
     * not, which is why a file found under any of them is the same mistake.
     */
    private function compareFormat(
        string $id,
        FormatStance $stance,
        ElementFormat $format,
        bool $found
    ): ?string {
        if ($stance->assertsPresence()) {
            return $found
                ? null
                : sprintf('%s: expected as %s, and nothing on disk is.', $id, $format->value);
        }

        if (! $found) {
            return null;
        }

        return match ($stance) {
            FormatStance::TODO => sprintf('%s: left to do as %s, and found in it — the todo is done, say so.', $id, $format->value),
            FormatStance::WAIVED => sprintf('%s: declared to do without %s, and found in it — the reason is stale.', $id, $format->value),
            default => sprintf('%s: found as %s, and the declaration says nothing about it — expected, to do, or why not?', $id, $format->value),
        };
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

        foreach ($this->getSources() as $source) {
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
                $this->getSources(),
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
