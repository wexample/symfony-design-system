<?php

namespace Wexample\SymfonyDesignSystem\Class;

use Wexample\SymfonyDesignSystem\Enum\ElementFormat;
use Wexample\SymfonyDesignSystem\Enum\FormatStance;

/**
 * The elements of the design system and the formats each is delivered in.
 *
 * What a registry file holds, what a scan produces, and what a page draws. It
 * says what is there, never what should be: read beside the natures an element
 * can have, a row found in one format alone is a question the collection cannot
 * ask itself.
 *
 * Entries are keyed by bundle and key together, so the inventories of several
 * bundles merge without one `bar` swallowing another.
 */
class ElementInventory
{
    /**
     * @var array<string, ElementEntry> id => entry
     */
    private array $entries = [];

    /**
     * @param string[] $unscannedPaths directories of the assets root the scan
     *                                 does not look at, qualified by alias, so
     *                                 that a page showing the inventory can say
     *                                 what it leaves out
     */
    public function __construct(
        private array $unscannedPaths = [],
    ) {
    }

    /**
     * Folds another inventory into this one. Same id, same element: the
     * occurrences are added and the declared fields of the newcomer win.
     */
    public function merge(self $other): void
    {
        foreach ($other->getEntries() as $entry) {
            $this->entries[$entry->getId()] = $entry;
        }

        $this->unscannedPaths = array_values(
            array_unique(
                array_merge($this->unscannedPaths, $other->getUnscannedPaths())
            )
        );
    }

    public function entry(
        string $source,
        string $key
    ): ElementEntry {
        return $this->entries[ElementEntry::buildId($source, $key)] ??= new ElementEntry($key, $source);
    }

    public function hasEntry(
        string $source,
        string $key
    ): bool {
        return isset($this->entries[ElementEntry::buildId($source, $key)]);
    }

    public function findEntry(
        string $source,
        string $key
    ): ?ElementEntry {
        return $this->entries[ElementEntry::buildId($source, $key)] ?? null;
    }

    /**
     * @return ElementEntry[] sorted by source then key, which groups an element
     *                        with the others of its bundle and directory
     */
    public function getEntries(): array
    {
        $entries = $this->entries;
        ksort($entries);

        return array_values($entries);
    }

    /**
     * @return array<string, ElementEntry[]> source alias => entries
     */
    public function getEntriesBySource(): array
    {
        $sources = [];

        foreach ($this->getEntries() as $entry) {
            $sources[$entry->source][] = $entry;
        }

        return $sources;
    }

    /**
     * @return array<string, ElementEntry[]> group name => entries, the empty
     *                                       group first since it holds the
     *                                       elements that belong to no family
     */
    public function getEntriesByGroup(): array
    {
        $groups = [];

        foreach ($this->getEntries() as $entry) {
            $groups[$entry->getGroup()][] = $entry;
        }

        ksort($groups);

        return $groups;
    }

    /**
     * The keys held by more than one bundle. Not an error — the loader tells
     * them apart — but the one thing a reader of the union would want pointed
     * at, whether it is an app redrawing a design system element or two things
     * that happen to share a name.
     *
     * @return array<string, string[]> key => aliases holding it
     */
    public function findSharedKeys(): array
    {
        $byKey = [];

        foreach ($this->entries as $entry) {
            $byKey[$entry->key][] = $entry->source;
        }

        return array_filter(
            $byKey,
            static fn (array $sources): bool => count($sources) > 1
        );
    }

    /**
     * The formats the registry knows about, in the order a table shows them.
     * The page holds no list of its own: a format added to the enum is a column
     * that appears.
     *
     * @return string[]
     */
    public function getFormatValues(): array
    {
        return array_map(
            static fn (ElementFormat $format): string => $format->value,
            ElementFormat::cases()
        );
    }

    /**
     * How many element-and-format pairs stand in each way — the only honest
     * summary now that a row is not judged by how many formats it holds.
     *
     * @return array<string, int> stance value => count
     */
    public function countByStance(): array
    {
        $counts = [];

        foreach (FormatStance::cases() as $stance) {
            $counts[$stance->value] = 0;
        }

        foreach ($this->entries as $entry) {
            foreach (ElementFormat::cases() as $format) {
                ++$counts[$entry->getStance($format)->value];
            }
        }

        return $counts;
    }

    /**
     * @return array<string, int> format value => how many elements have it
     */
    public function countByFormat(): array
    {
        $counts = [];

        foreach (ElementFormat::cases() as $format) {
            $counts[$format->value] = count(
                array_filter(
                    $this->entries,
                    static fn (ElementEntry $entry): bool => $entry->has($format)
                )
            );
        }

        return $counts;
    }

    /**
     * @return array<int, int> number of formats => how many elements have that many
     */
    public function countByFormatsCount(): array
    {
        $counts = array_fill(0, count(ElementFormat::cases()) + 1, 0);

        foreach ($this->entries as $entry) {
            ++$counts[$entry->countFormats()];
        }

        return $counts;
    }

    public function countEntries(): int
    {
        return count($this->entries);
    }

    /**
     * @return string[]
     */
    public function getUnscannedPaths(): array
    {
        return $this->unscannedPaths;
    }

    /**
     * What gets written to a registry file. The counts are left out on purpose —
     * they are one `count()` away for whoever reads the file, and a derived
     * number written down is a number that can disagree with the rows above it.
     */
    public function toArray(): array
    {
        return [
            'elements' => array_map(
                static fn (ElementEntry $entry): array => $entry->toArray(),
                $this->getEntries()
            ),
            'unscanned_paths' => $this->getUnscannedPaths(),
        ];
    }

    public static function fromArray(array $data): self
    {
        $inventory = new self($data['unscanned_paths'] ?? []);

        foreach ($data['elements'] ?? [] as $element) {
            $entry = ElementEntry::fromArray($element);
            $inventory->entries[$entry->getId()] = $entry;
        }

        return $inventory;
    }
}
