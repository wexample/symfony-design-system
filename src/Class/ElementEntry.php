<?php

namespace Wexample\SymfonyDesignSystem\Class;

use Wexample\SymfonyDesignSystem\Enum\ElementFormat;

/**
 * One element of the design system, and every format it was found in.
 *
 * Nothing here was declared: the entry exists because files carrying its key
 * were found on disk, and it holds what they were rather than what the element
 * ought to be. Saying which formats an element *should* have is the job of the
 * declaration that will replace this scan.
 */
class ElementEntry
{
    /**
     * @var array<string, string[]> format value => what was found, paths
     *                              relative to `assets/`, or function names
     */
    private array $occurrences = [];

    public function __construct(
        public readonly string $key,
    ) {
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
            'group' => $this->getGroup(),
            'formats' => $this->getOccurrences(),
            'formats_count' => $this->countFormats(),
        ];
    }
}
