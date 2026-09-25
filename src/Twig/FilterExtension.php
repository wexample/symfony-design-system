<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The filters of a server table, kept in the page's query: what each one holds
 * is read from it, and each option is a link to the same page with that option
 * turned. The controller reads the same query to narrow what it lists. The
 * twin of FilterHelper.ts, which does the same to a value held by a vue.
 */
class FilterExtension extends AbstractExtension
{
    public function __construct(
        private readonly RequestStack $requestStack,
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
    public function filterSelected(string $key): array
    {
        $value = $this->getRequest()?->query->all()[$key] ?? null;

        if (null === $value || '' === $value) {
            return [];
        }

        return array_values(array_map('strval', (array) $value));
    }

    /**
     * Its name while it narrows nothing, then what it narrows to: the one
     * value, or the first and how many more.
     */
    public function filterSummary(array $filter): string
    {
        $selected = $this->filterSelected($filter['key']);

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

        return $filter['label'].': '.$shown.(count($selected) > 1 ? ' +'.(count($selected) - 1) : '');
    }

    /**
     * The same page with `value` added to or removed from a multiple filter,
     * chosen or cleared in a single one.
     */
    public function filterToggleUrl(array $filter, string $value): string
    {
        $selected = $this->filterSelected($filter['key']);
        $on = in_array($value, $selected, true);

        if ($filter['multiple'] ?? false) {
            $kept = $on ? array_values(array_diff($selected, [$value])) : [...$selected, $value];
        } else {
            $kept = $on ? [] : [$value];
        }

        return $this->urlWith($filter['key'], ($filter['multiple'] ?? false) ? $kept : ($kept[0] ?? null));
    }

    public function filterClearUrl(string $key): string
    {
        return $this->urlWith($key, null);
    }

    // The current address with one key of its query set, or dropped when it
    // holds nothing — and the page number dropped too: a list narrowed
    // differently starts again from its first page.
    private function urlWith(string $key, string|array|null $value): string
    {
        $request = $this->getRequest();

        if (! $request) {
            return '#';
        }

        $query = $request->query->all();
        unset($query['page']);

        if (null === $value || [] === $value) {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }

        $path = $request->getBaseUrl().$request->getPathInfo();

        return $query ? $path.'?'.http_build_query($query) : $path;
    }

    private function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}
