<?php

namespace Wexample\SymfonyDesignSystem\Helper;

use Collator;
use DateTimeInterface;

/**
 * The order of a table's rows, as the server table and the controller behind
 * it read it: `['key' => …, 'direction' => 'asc'|'desc']`, null for the order
 * the rows were given in. The twin of SortHelper.ts, which the vue table
 * follows: a change to either is a change to both.
 */
class SortHelper
{
    public const string QUERY_KEY = 'sort';
    public const string QUERY_DIRECTION = 'direction';
    public const string ASC = 'asc';
    public const string DESC = 'desc';

    public static function equals(?array $a, ?array $b): bool
    {
        if (! $a || ! $b) {
            return ! $a && ! $b;
        }

        return $a['key'] === $b['key'] && $a['direction'] === $b['direction'];
    }

    /**
     * The order once the header of `key` is pressed: ascending, then
     * descending, then back to the default order. On the column the default
     * order already sorts, the press turns it the other way, and the next one
     * comes back to it.
     */
    public static function next(?array $current, string $key, ?array $default = null): ?array
    {
        $state = $current ?? $default;

        if (! $state || $state['key'] !== $key) {
            return ['key' => $key, 'direction' => self::ASC];
        }

        if (self::equals($state, $default)) {
            return ['key' => $key, 'direction' => self::ASC === $state['direction'] ? self::DESC : self::ASC];
        }

        return self::ASC === $state['direction'] ? ['key' => $key, 'direction' => self::DESC] : $default;
    }

    // What `aria-sort` says of the header of `key`.
    public static function aria(?array $state, string $key): string
    {
        if (! $state || $state['key'] !== $key) {
            return 'none';
        }

        return self::ASC === $state['direction'] ? 'ascending' : 'descending';
    }

    /**
     * The order a page's query asks for, if it names one of the `allowed` keys,
     * the default one otherwise. What comes out is safe to hand a query
     * builder: a key the page did not allow never gets through, which is what
     * keeps a crafted address out of an ORDER BY.
     *
     * @param string[] $allowed
     */
    public static function fromQuery(array $query, array $allowed, ?array $default = null): ?array
    {
        $key = $query[self::QUERY_KEY] ?? null;

        if (! is_string($key) || ! in_array($key, $allowed, true)) {
            return $default;
        }

        return [
            'key' => $key,
            'direction' => self::DESC === ($query[self::QUERY_DIRECTION] ?? null) ? self::DESC : self::ASC,
        ];
    }

    /**
     * The keys a table lets its query sort by: those of its sortable columns,
     * and the default one, which may sort on a field no column shows.
     *
     * @param array[] $columns normalized columns, with `sortable` and `sort_key`
     *
     * @return string[]
     */
    public static function allowedKeys(array $columns, ?array $default = null): array
    {
        $allowed = array_column(array_filter($columns, fn (array $column) => $column['sortable'] ?? false), 'sort_key');

        if ($default) {
            $allowed[] = $default['key'];
        }

        return $allowed;
    }

    /**
     * `path` with the query set to the order `sort`, its keys dropped for the
     * default one — and the page number dropped too: a list ordered
     * differently starts again from its first page. The rest of the query, the
     * filters, is kept.
     */
    public static function url(string $path, array $query, ?array $sort, ?array $default = null): string
    {
        unset($query['page'], $query[self::QUERY_KEY], $query[self::QUERY_DIRECTION]);

        if ($sort && ! self::equals($sort, $default)) {
            $query[self::QUERY_KEY] = $sort['key'];
            $query[self::QUERY_DIRECTION] = $sort['direction'];
        }

        return $query ? $path.'?'.http_build_query($query) : $path;
    }

    // Nothing to sort on: such a row goes last whichever way the list runs.
    public static function isEmpty(mixed $value): bool
    {
        return null === $value
            || (is_string($value) && '' === trim($value))
            || (is_float($value) && is_nan($value));
    }

    // The field `path` of a row, dots reaching into what it holds.
    public static function valueAt(mixed $row, string $path): mixed
    {
        foreach (explode('.', $path) as $part) {
            if (is_array($row)) {
                $row = $row[$part] ?? null;
            } elseif (is_object($row)) {
                $row = $row->$part ?? null;
            } else {
                return null;
            }
        }

        return $row;
    }

    /**
     * Two values neither of which is empty: numbers and dates as quantities,
     * anything else as words of the locale, figures inside a text read as
     * numbers.
     */
    public static function compareValues(mixed $a, mixed $b, Collator $collator): int
    {
        $left = $a instanceof DateTimeInterface ? $a->getTimestamp() : (is_bool($a) ? (int) $a : $a);
        $right = $b instanceof DateTimeInterface ? $b->getTimestamp() : (is_bool($b) ? (int) $b : $b);

        if ((is_int($left) || is_float($left)) && (is_int($right) || is_float($right))) {
            return $left <=> $right;
        }

        return (int) $collator->compare((string) $left, (string) $right);
    }

    public static function collator(?string $locale): Collator
    {
        $collator = new Collator($locale ?: 'en');
        $collator->setAttribute(Collator::NUMERIC_COLLATION, Collator::ON);

        return $collator;
    }

    /**
     * The rows in the order `state` asks for: rows comparing equal keep the
     * order they came in, empty values go last both ways, and a group row stays
     * where it is, the rows under it sorted among themselves.
     */
    public static function apply(array $rows, ?array $state, ?string $locale = null): array
    {
        if (! $state) {
            return $rows;
        }

        $collator = self::collator($locale);
        $sign = self::DESC === $state['direction'] ? -1 : 1;
        $compare = static function (mixed $a, mixed $b) use ($state, $collator, $sign): int {
            $left = self::valueAt($a, $state['key']);
            $right = self::valueAt($b, $state['key']);
            $leftEmpty = self::isEmpty($left);
            $rightEmpty = self::isEmpty($right);

            if ($leftEmpty || $rightEmpty) {
                return (int) $leftEmpty - (int) $rightEmpty;
            }

            return $sign * self::compareValues($left, $right, $collator);
        };

        $sorted = [];
        $run = [];

        foreach ($rows as $row) {
            if (is_array($row) && array_key_exists('group', $row)) {
                usort($run, $compare);
                array_push($sorted, ...$run);
                $sorted[] = $row;
                $run = [];
            } else {
                $run[] = $row;
            }
        }

        usort($run, $compare);

        return [...$sorted, ...$run];
    }
}
