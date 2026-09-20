<?php

namespace Wexample\SymfonyDesignSystem\Class;

use Wexample\SymfonyDesignSystem\Enum\ElementFormat;

/**
 * What a bundle says about one of its elements, by hand, in a yaml file.
 *
 * It holds what no scan can know — what the element is, what it is for, and
 * for each format either that the element is expected in it or why it does
 * without. It holds no file paths: those are on disk, the scan reads them, and
 * writing them here would only give the two a way to disagree that nobody asked
 * for.
 *
 * Seeded once by `design-system:seed-elements`, never rewritten by anything.
 */
class ElementDeclaration
{
    /**
     * A format is expected — the element must be found in it.
     */
    final public const bool EXPECTED = true;

    /**
     * @param array<string, bool|string> $formats format value => EXPECTED, or
     *                                            a sentence saying why the
     *                                            element does without it
     */
    public function __construct(
        public readonly string $key,
        public readonly ?string $nature = null,
        public readonly ?string $description = null,
        public readonly array $formats = [],
    ) {
    }

    public function isExpected(ElementFormat $format): bool
    {
        return ($this->formats[$format->value] ?? null) === self::EXPECTED;
    }

    public function getAbsentJustification(ElementFormat $format): ?string
    {
        $value = $this->formats[$format->value] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * A format neither expected nor justified is a decision not yet made, and
     * the check names it rather than letting it pass as either.
     */
    public function isUndecided(ElementFormat $format): bool
    {
        return ! $this->isExpected($format)
            && $this->getAbsentJustification($format) === null;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'nature' => $this->nature,
            'description' => $this->description,
            'formats' => $this->formats,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['key'],
            $data['nature'] ?? null,
            $data['description'] ?? null,
            (array) ($data['formats'] ?? []),
        );
    }
}
