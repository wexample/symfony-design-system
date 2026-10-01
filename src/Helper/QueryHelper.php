<?php

namespace Wexample\SymfonyDesignSystem\Helper;

/**
 * What a server table keeps in the page's query — its filters, its order —
 * read and written in one place. A table given a `query_key` keeps all of it
 * under that key (`?patients[sort]=name&patients[owner][0]=a`), so two tables
 * on one page do not share an order or a filter; without one, at the top of
 * the query, as a page holding a single table expects.
 */
class QueryHelper
{
    public const string PAGE = 'page';

    // The part of the query that belongs to the table.
    public static function scoped(array $query, ?string $queryKey = null): array
    {
        if (null === $queryKey) {
            return $query;
        }

        $scoped = $query[$queryKey] ?? [];

        return is_array($scoped) ? $scoped : [];
    }

    /**
     * `path` with the table's part of the query changed: each entry of `set`
     * written, or dropped when it holds nothing. The page number goes too: a
     * list narrowed or ordered differently starts again from its first page.
     * The rest of the query — another table's part — is kept as it is.
     *
     * @param array<string, string|array|null> $set
     */
    public static function url(string $path, array $query, array $set, ?string $queryKey = null): string
    {
        $scoped = self::scoped($query, $queryKey);
        unset($scoped[self::PAGE]);

        foreach ($set as $key => $value) {
            if (null === $value || [] === $value) {
                unset($scoped[$key]);
            } else {
                $scoped[$key] = $value;
            }
        }

        if (null === $queryKey) {
            $query = $scoped;
        } elseif ($scoped) {
            $query[$queryKey] = $scoped;
        } else {
            unset($query[$queryKey]);
        }

        return $query ? $path.'?'.http_build_query($query) : $path;
    }
}
