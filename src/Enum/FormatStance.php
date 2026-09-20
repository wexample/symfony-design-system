<?php

namespace Wexample\SymfonyDesignSystem\Enum;

/**
 * Where an element stands on one of the formats, as its declaration puts it.
 *
 * What tells the four apart is whose move it is next: the element is there and
 * nothing is owed; someone should build it; nobody should, and here is why; or
 * nobody has looked yet.
 *
 * Three of the four assert the same thing about the disk — that the format is
 * not carried — so the check treats them alike and they differ only in what
 * they mean and in what a page draws.
 */
enum FormatStance: string
{
    /**
     * Declared `true`. The element must be found in the format; a check fails
     * when it is not.
     */
    case EXPECTED = 'expected';

    /**
     * Declared `todo`, optionally followed by a note. The format is wanted and
     * missing, and saying so is the point: an acknowledged gap is work someone
     * can pick up, where an undecided one is nobody's.
     */
    case TODO = 'todo';

    /**
     * Declared as a sentence. The element does without the format on purpose,
     * and the sentence is the reason.
     */
    case WAIVED = 'waived';

    /**
     * Not in the file at all. Nobody has ruled, and a check names it rather
     * than letting it pass as either of the other three.
     */
    case UNDECIDED = 'undecided';

    /**
     * The word a declaration writes to mean TODO, alone or ahead of a note.
     */
    final public const string TODO_MARKER = 'todo';

    /**
     * Reads one value of a declaration's `formats` map.
     */
    public static function read(bool|string|null $value): self
    {
        if ($value === true) {
            return self::EXPECTED;
        }

        if (! is_string($value) || trim($value) === '') {
            return self::UNDECIDED;
        }

        return self::isTodo($value) ? self::TODO : self::WAIVED;
    }

    /**
     * The note a value carries, if any: what follows the marker for a TODO,
     * the whole sentence for a WAIVED, nothing otherwise.
     */
    public static function readNote(bool|string|null $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        if (! self::isTodo($value)) {
            return trim($value) === '' ? null : trim($value);
        }

        $note = trim(substr(trim($value), strlen(self::TODO_MARKER)));
        $note = ltrim($note, " \t-—:,.");

        return $note === '' ? null : $note;
    }

    /**
     * The marker is a word, not a prefix: a justification opening on `todos of
     * the world` is a justification, and only `todo` on its own or followed by
     * a separator is the marker.
     */
    private static function isTodo(string $value): bool
    {
        $value = strtolower(trim($value));

        return $value === self::TODO_MARKER
            || (bool) preg_match('/^' . self::TODO_MARKER . '[\s\-—:,.]/', $value);
    }

    /**
     * Whether the declaration says the format is carried. Only EXPECTED does;
     * the other three all say it is not, which is why a file appearing under a
     * TODO or a WAIVED is the same kind of mistake.
     */
    public function assertsPresence(): bool
    {
        return $this === self::EXPECTED;
    }
}
