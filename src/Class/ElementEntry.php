<?php

namespace Wexample\SymfonyDesignSystem\Class;

use Wexample\SymfonyDesignSystem\Enum\ElementFormat;
use Wexample\SymfonyDesignSystem\Enum\FormatStance;

/**
 * One element of the design system as the registry knows it: what was declared
 * about it, and what was found for it.
 *
 * The declared part — nature, description, and where the bundle stands on each
 * format — comes from its yaml. The found part — the files behind each format —
 * comes from the scan. The two are put together only once they agree, so an
 * entry never says both that a format is expected and that nothing carries it.
 *
 * An element is identified by its bundle and its key together. Two bundles may
 * each hold a `bar`; the loader resolves them by alias, so they are two
 * elements and not one seen twice.
 */
class ElementEntry
{
    /**
     * @var array<string, string[]> format value => what was found, paths
     *                              qualified by the bundle alias, or function names
     */
    private array $occurrences = [];

    /**
     * @var array<string, string> format value => where the declaration stands
     *                            on it, as a FormatStance value
     */
    private array $stances = [];

    /**
     * @var array<string, string> format value => what the declaration says in
     *                            its own words about it
     */
    private array $notes = [];

    public function __construct(
        public readonly string $key,
        public readonly string $source,
        public ?string $description = null,
    ) {
    }

    /**
     * What tells this element from every other in a merged inventory.
     */
    public function getId(): string
    {
        return self::buildId($this->source, $this->key);
    }

    public static function buildId(
        string $source,
        string $key
    ): string {
        return $source . ':' . $key;
    }

    public function add(
        ElementFormat $format,
        string $occurrence
    ): void {
        $found = &$this->occurrences[$format->value];
        $found ??= [];

        if (! in_array($occurrence, $found, true)) {
            $found[] = $occurrence;
        }
    }

    public function has(ElementFormat $format): bool
    {
        return ! empty($this->occurrences[$format->value]);
    }

    /**
     * @return string[]
     */
    public function get(ElementFormat $format): array
    {
        return $this->occurrences[$format->value] ?? [];
    }

    public function setStance(
        ElementFormat $format,
        FormatStance $stance,
        ?string $note = null
    ): void {
        $this->stances[$format->value] = $stance->value;

        if ($note !== null) {
            $this->notes[$format->value] = $note;
        }
    }

    public function getStance(ElementFormat $format): FormatStance
    {
        return FormatStance::tryFrom($this->stances[$format->value] ?? '')
            ?? FormatStance::UNDECIDED;
    }

    public function getNote(ElementFormat $format): ?string
    {
        return $this->notes[$format->value] ?? null;
    }

    /**
     * @return array<string, string> format value => stance value
     */
    public function getStances(): array
    {
        return $this->stances;
    }

    /**
     * @return array<string, string>
     */
    public function getNotes(): array
    {
        return $this->notes;
    }

    /**
     * How many of the formats the element was found in — the number the
     * inventory is sorted and read by, since an element found in one format
     * alone is either a gap or a deliberate exception, and both need looking at.
     */
    public function countFormats(): int
    {
        return count($this->occurrences);
    }

    /**
     * The part of the key before its last segment, which is the directory the
     * element sits in for every format at once — `form` for `form/text-input`,
     * nothing for `button`.
     */
    public function getGroup(): string
    {
        $segments = explode('/', $this->key);
        array_pop($segments);

        return implode('/', $segments);
    }

    public function getShortName(): string
    {
        $segments = explode('/', $this->key);

        return end($segments);
    }

    /**
     * Every format, including the ones the element is missing, so that whoever
     * draws a row has the same columns for all of them.
     *
     * @return array<string, string[]> format value => what was found
     */
    public function getOccurrences(): array
    {
        $occurrences = [];

        foreach (ElementFormat::cases() as $format) {
            $occurrences[$format->value] = $this->get($format);
        }

        return $occurrences;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'source' => $this->source,
            'description' => $this->description,
            'formats' => array_filter($this->getOccurrences()),
            'stances' => $this->stances,
            'notes' => $this->notes,
        ];
    }

    public static function fromArray(array $data): self
    {
        $entry = new self(
            $data['key'],
            $data['source'],
            $data['description'] ?? null,
        );

        foreach ($data['formats'] ?? [] as $value => $occurrences) {
            $format = ElementFormat::tryFrom($value);

            // A format dropped from the enum leaves its occurrences behind in a
            // registry written before that, and they are ignored rather than
            // fatal: the file is regenerated, not migrated.
            if ($format === null) {
                continue;
            }

            foreach ($occurrences as $occurrence) {
                $entry->add($format, $occurrence);
            }
        }

        foreach ($data['stances'] ?? [] as $value => $stance) {
            $format = ElementFormat::tryFrom($value);
            $stance = FormatStance::tryFrom((string) $stance);

            if ($format !== null && $stance !== null) {
                $entry->setStance($format, $stance, $data['notes'][$value] ?? null);
            }
        }

        return $entry;
    }
}
