<?php

namespace Wexample\SymfonyDesignSystem\Helper;

/**
 * What changed between two versions of a text, line by line — and, in a line
 * changed, word by word —, as the text diff draws it (components/diff-text).
 * The twin of DiffTextHelper.ts, which the vue diff follows: the same rows for
 * the same two texts, a change to either is a change to both
 * (tests/Unit/Helper/DiffTextHelperTest.php, tests/js/DiffTextHelper.test.ts).
 *
 * A line: `['type' => 'same'|'add'|'remove', 'text', 'old', 'new', 'parts'?]`,
 * `old` and `new` its numbers in either text (null on the side it is not in),
 * `parts` its words `['text', 'changed']` when it faces the line it changed
 * from. A fold: `['type' => 'fold', 'count', 'old', 'new', 'lines']`.
 */
class DiffTextHelper
{
    public const string SAME = 'same';

    public const string ADD = 'add';

    public const string REMOVE = 'remove';

    public const string FOLD = 'fold';

    /**
     * The shortest edit from one sequence to the other (Myers); in a run of
     * changes, what goes before what comes.
     *
     * @return list<array{type: string, a?: mixed, b?: mixed, ai?: int, bi?: int}>
     */
    public static function sequence(array $a, array $b): array
    {
        $n = count($a);
        $m = count($b);
        $max = $n + $m;
        $offset = $max + 1;
        $v = array_fill(0, 2 * $max + 3, 0);
        $trace = [];
        $found = false;

        for ($d = 0; $d <= $max && ! $found; ++$d) {
            $trace[] = $v;

            for ($k = -$d; $k <= $d; $k += 2) {
                $x = ($k === -$d || ($k !== $d && $v[$offset + $k - 1] < $v[$offset + $k + 1]))
                    ? $v[$offset + $k + 1]
                    : $v[$offset + $k - 1] + 1;
                $y = $x - $k;

                while ($x < $n && $y < $m && $a[$x] === $b[$y]) {
                    ++$x;
                    ++$y;
                }

                $v[$offset + $k] = $x;

                if ($x >= $n && $y >= $m) {
                    $found = true;

                    break;
                }
            }
        }

        $edits = [];
        $x = $n;
        $y = $m;

        for ($d = count($trace) - 1; $d >= 0; --$d) {
            $previous = $trace[$d];
            $k = $x - $y;
            $previousK = ($k === -$d || ($k !== $d && $previous[$offset + $k - 1] < $previous[$offset + $k + 1])) ? $k + 1 : $k - 1;
            $previousX = $previous[$offset + $previousK];
            $previousY = $previousX - $previousK;

            while ($x > $previousX && $y > $previousY) {
                $edits[] = ['type' => self::SAME, 'a' => $a[$x - 1], 'b' => $b[$y - 1], 'ai' => $x - 1, 'bi' => $y - 1];
                --$x;
                --$y;
            }

            if ($d > 0) {
                $edits[] = $x === $previousX
                    ? ['type' => self::ADD, 'b' => $b[$y - 1], 'bi' => $y - 1]
                    : ['type' => self::REMOVE, 'a' => $a[$x - 1], 'ai' => $x - 1];
            }

            $x = $previousX;
            $y = $previousY;
        }

        $edits = array_reverse($edits);

        // In each run of changes, its removals first, then its additions.
        $ordered = [];
        $run = [];
        $flush = static function () use (&$ordered, &$run): void {
            foreach ([self::REMOVE, self::ADD] as $type) {
                foreach ($run as $edit) {
                    if ($edit['type'] === $type) {
                        $ordered[] = $edit;
                    }
                }
            }
            $run = [];
        };

        foreach ($edits as $edit) {
            if (self::SAME === $edit['type']) {
                $flush();
                $ordered[] = $edit;
            } else {
                $run[] = $edit;
            }
        }
        $flush();

        return $ordered;
    }

    /**
     * @return list<string>
     */
    public static function splitLines(?string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", (string) $text);

        return '' === $text ? [] : explode("\n", $text);
    }

    /**
     * A line as its words, the space and the marks between them kept.
     *
     * @return list<string>
     */
    public static function tokens(string $line): array
    {
        preg_match_all('/\s+|[\p{L}\p{N}_]+|[^\s\p{L}\p{N}_]/u', $line, $matches);

        return $matches[0];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function lines(?string $before, ?string $after, bool $words = true): array
    {
        $lines = array_map(static fn (array $edit): array => [
            'type' => $edit['type'],
            'text' => (string) (self::ADD === $edit['type'] ? $edit['b'] : $edit['a']),
            'old' => isset($edit['ai']) ? $edit['ai'] + 1 : null,
            'new' => isset($edit['bi']) ? $edit['bi'] + 1 : null,
        ], self::sequence(self::splitLines($before), self::splitLines($after)));

        if (! $words) {
            return $lines;
        }

        // Each run of changes: its i-th removal faces its i-th addition.
        $count = count($lines);

        for ($start = 0; $start < $count;) {
            if (self::SAME === $lines[$start]['type']) {
                ++$start;

                continue;
            }

            $end = $start;

            while ($end < $count && self::SAME !== $lines[$end]['type']) {
                ++$end;
            }

            $removed = [];
            $added = [];

            for ($i = $start; $i < $end; ++$i) {
                if (self::REMOVE === $lines[$i]['type']) {
                    $removed[] = $i;
                } else {
                    $added[] = $i;
                }
            }

            for ($i = 0; $i < min(count($removed), count($added)); ++$i) {
                $edits = self::sequence(self::tokens($lines[$removed[$i]]['text']), self::tokens($lines[$added[$i]]['text']));
                $lines[$removed[$i]]['parts'] = self::parts($edits, self::REMOVE);
                $lines[$added[$i]]['parts'] = self::parts($edits, self::ADD);
            }

            $start = $end;
        }

        return $lines;
    }

    /**
     * The lines to read: the changes and `context` lines kept around each,
     * the kept lines further away folded. Below zero, nothing folds.
     *
     * @return list<array<string, mixed>>
     */
    public static function rows(?string $before, ?string $after, int $context = 3, bool $words = true): array
    {
        $lines = self::lines($before, $after, $words);

        if ($context < 0) {
            return $lines;
        }

        $count = count($lines);
        $near = array_fill(0, $count, false);

        foreach ($lines as $index => $line) {
            if (self::SAME !== $line['type']) {
                for ($i = max(0, $index - $context); $i <= min($count - 1, $index + $context); ++$i) {
                    $near[$i] = true;
                }
            }
        }

        $rows = [];
        $folded = [];
        $flush = static function () use (&$rows, &$folded): void {
            if ([] !== $folded) {
                $rows[] = ['type' => self::FOLD, 'count' => count($folded), 'old' => $folded[0]['old'] ?? 0, 'new' => $folded[0]['new'] ?? 0, 'lines' => $folded];
                $folded = [];
            }
        };

        foreach ($lines as $index => $line) {
            if (self::SAME === $line['type'] && ! $near[$index]) {
                $folded[] = $line;
            } else {
                $flush();
                $rows[] = $line;
            }
        }
        $flush();

        return $rows;
    }

    /**
     * @return array{added: int, removed: int}
     */
    public static function stats(array $rows): array
    {
        $types = array_column($rows, 'type');

        return [
            'added' => count(array_keys($types, self::ADD, true)),
            'removed' => count(array_keys($types, self::REMOVE, true)),
        ];
    }

    /**
     * Side by side: a kept line both sides, a run of changes its removals on
     * the left facing its additions on the right; a fold across both.
     *
     * @return list<array{fold?: array, left: ?array, right: ?array}>
     */
    public static function sideBySide(array $rows): array
    {
        $result = [];
        $removed = [];
        $added = [];
        $flush = static function () use (&$result, &$removed, &$added): void {
            for ($i = 0; $i < max(count($removed), count($added)); ++$i) {
                $result[] = ['left' => $removed[$i] ?? null, 'right' => $added[$i] ?? null];
            }
            $removed = [];
            $added = [];
        };

        foreach ($rows as $row) {
            if (self::REMOVE === $row['type']) {
                $removed[] = $row;
            } elseif (self::ADD === $row['type']) {
                $added[] = $row;
            } else {
                $flush();
                $result[] = self::FOLD === $row['type'] ? ['fold' => $row, 'left' => null, 'right' => null] : ['left' => $row, 'right' => $row];
            }
        }
        $flush();

        return $result;
    }

    /**
     * @return list<array{text: string, changed: bool}>
     */
    private static function parts(array $edits, string $side): array
    {
        $parts = [];

        foreach ($edits as $edit) {
            if (self::SAME !== $edit['type'] && $edit['type'] !== $side) {
                continue;
            }

            $text = (string) (self::REMOVE === $side ? ($edit['a'] ?? '') : ($edit['b'] ?? $edit['a'] ?? ''));
            $changed = self::SAME !== $edit['type'];
            $last = array_key_last($parts);

            if (null !== $last && $parts[$last]['changed'] === $changed) {
                $parts[$last]['text'] .= $text;
            } else {
                $parts[] = ['text' => $text, 'changed' => $changed];
            }
        }

        return $parts;
    }
}
