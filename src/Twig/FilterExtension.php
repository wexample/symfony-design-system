<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Helper\QueryHelper;
use Wexample\SymfonyTranslations\Translation\Translator;

/**
 * The filters of a server table, kept in the page's query: what each one holds
 * is read from it, and each option is a link to the same page with that option
 * turned. The controller reads the same query to narrow what it lists. The
 * twin of FilterHelper.ts, which does the same to a value held by a vue.
 *
 * Every function takes the table's `query_key`, when it has one: its filters
 * are then read and written under that key only (see QueryHelper).
 */
class FilterExtension extends AbstractExtension
{
    private const string TRANSLATION_PREFIX = 'WexampleSymfonyDesignSystemBundle.common.system'.Translator::DOMAIN_SEPARATOR.'frontend.filter.';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Translator $translator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('filter_selected', $this->filterSelected(...)),
            new TwigFunction('filter_summary', $this->filterSummary(...)),
            new TwigFunction('filter_toggle_url', $this->filterToggleUrl(...)),
            new TwigFunction('filter_clear_url', $this->filterClearUrl(...)),
        ];
    }

    /**
     * @return string[]
     */
    public function filterSelected(string $key, ?string $queryKey = null): array
    {
        $value = QueryHelper::scoped($this->getRequest()?->query->all() ?? [], $queryKey)[$key] ?? null;

        if (null === $value || '' === $value) {
            return [];
        }

        return array_values(array_map('strval', (array) $value));
    }

    /**
     * Its name while it narrows nothing, then what it narrows to: the one
     * value, or the first and how many more — worded by the translations.
     */
    public function filterSummary(array $filter, ?string $queryKey = null): string
    {
        $selected = $this->filterSelected($filter['key'], $queryKey);

        if (! $selected) {
            return $filter['label'];
        }

        $shown = $selected[0];

        foreach ($filter['options'] ?? [] as $option) {
            if ((string) $option['value'] === $selected[0]) {
                $shown = $option['label'];

                break;
            }
        }

        $more = count($selected) - 1;

        return $this->translator->trans(
            self::TRANSLATION_PREFIX.($more ? 'summary_more' : 'summary'),
            ['%label%' => $filter['label'], '%value%' => $shown, '%more%' => $more]
        );
    }

    /**
     * The same page with `value` added to or removed from a multiple filter,
     * chosen or cleared in a single one.
     */
    public function filterToggleUrl(array $filter, string $value, ?string $queryKey = null): string
    {
        $selected = $this->filterSelected($filter['key'], $queryKey);
        $on = in_array($value, $selected, true);

        if ($filter['multiple'] ?? false) {
            $kept = $on ? array_values(array_diff($selected, [$value])) : [...$selected, $value];
        } else {
            $kept = $on ? [] : [$value];
        }

        return $this->urlWith($filter['key'], ($filter['multiple'] ?? false) ? $kept : ($kept[0] ?? null), $queryKey);
    }

    public function filterClearUrl(string $key, ?string $queryKey = null): string
    {
        return $this->urlWith($key, null, $queryKey);
    }

    // The current address with one filter set, or dropped when it holds
    // nothing — the page number dropped with it.
    private function urlWith(string $key, string|array|null $value, ?string $queryKey): string
    {
        $request = $this->getRequest();

        if (! $request) {
            return '#';
        }

        return QueryHelper::url($request->getBaseUrl().$request->getPathInfo(), $request->query->all(), [$key => $value], $queryKey);
    }

    private function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}
