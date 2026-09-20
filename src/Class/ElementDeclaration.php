<?php

namespace Wexample\SymfonyDesignSystem\Class;

use Wexample\SymfonyDesignSystem\Enum\ElementFormat;
use Wexample\SymfonyDesignSystem\Enum\FormatStance;

/**
 * What a bundle says about one of its elements, by hand, in a yaml file.
 *
 * It holds what no scan can know — what the element is, what it is for, and
 * where it stands on each format. It holds no file paths: those are on disk,
 * the scan reads them, and writing them here would only give the two a way to
 * disagree that nobody asked for.
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
     * @param array<string, bool|string> $formats format value => `true` when
     *                                            expected, `todo` with an
     *                                            optional note when wanted and
     *                                            missing, a sentence when the
     *                                            element does without on
     *                                            purpose. A format left out is
     *                                            a decision not yet made.
     */
    public function __construct(
        public readonly string $key,
        public readonly ?string $nature = null,
        public readonly ?string $description = null,
        public readonly array $formats = [],
    ) {
    }

    public function getStance(ElementFormat $format): FormatStance
    {
        return FormatStance::read($this->formats[$format->value] ?? null);
    }

    /**
     * What the declaration adds in its own words: the reason behind a waiver,
     * the note behind a todo, nothing otherwise.
     */
    public function getNote(ElementFormat $format): ?string
    {
        return FormatStance::readNote($this->formats[$format->value] ?? null);
    }

    public function isExpected(ElementFormat $format): bool
    {
        return $this->getStance($format) === FormatStance::EXPECTED;
    }

    public function isUndecided(ElementFormat $format): bool
    {
        return $this->getStance($format) === FormatStance::UNDECIDED;
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
