<?php

namespace Wexample\SymfonyDesignSystem\Class;

use Wexample\SymfonyDesignSystem\Enum\ElementFormat;

/**
 * The elements of the design system and the formats each is delivered in.
 *
 * What the registry file holds, and what a scan produces. It says what is there,
 * never what should be: read beside the natures an element can have, a row found
 * in one format alone is a question the collection cannot ask itself.
 */
class ElementInventory
{
    /**
     * @var array<string, ElementEntry>
     */
    private array $entries = [];

    /**
     * @param string[] $unscannedPaths directories of `assets/` this scan does
     *                                 not look at, so that a page showing the
     *                                 inventory can say what it leaves out
     */
    public function __construct(
        private readonly array $unscannedPaths = [],
    ) {
    }

    public function entry(string $key): ElementEntry
    {
        return $this->entries[$key] ??= new ElementEntry($key);
    }

    public function hasEntry(string $key): bool
    {
        return isset($this->entries[$key]);
    }

    /**
     * @return ElementEntry[] sorted by key, which groups an element with the
     *                        others of its directory
     */
    public function getEntries(): array
    {
        $entries = $this->entries;
        ksort($entries);

        return array_values($entries);
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
     * The formats the scan knows about, in the order a table shows them. The
     * page holds no list of its own: a format added to the enum is a column
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
        $counts = array_fill(1, count(ElementFormat::cases()), 0);

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
     * What gets written to the registry file: what was found, and what was not
     * looked at. The counts are left out on purpose — they are one `count()`
     * away for whoever reads the file, and a derived number written down is a
     * number that can disagree with the rows above it.
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
            $inventory->entries[$element['key']] = ElementEntry::fromArray($element);
        }

        return $inventory;
    }
}
