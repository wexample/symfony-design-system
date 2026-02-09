<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\TwigFunction;

class BreadcrumbExtension extends AbstractTemplateExtension
{
    private const STACK_ATTRIBUTE = '_breadcrumb_stack';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'breadcrumb',
                function (
                    Environment $twig,
                    array $items,
                    array $options = [],
                ) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/breadcrumb.html.twig',
                        [
                            'items' => $this->normalizeItems($items),
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
            new TwigFunction(
                'breadcrumb_append_route',
                [$this, 'breadcrumbAppendRoute']
            ),
            new TwigFunction(
                'breadcrumb_stack',
                [$this, 'breadcrumbStack']
            ),
            new TwigFunction(
                'breadcrumb_render',
                function (Environment $twig, array $options = []) {
                    return $this->renderTemplate(
                        $twig,
                        '@WexampleSymfonyDesignSystemBundle/components/breadcrumb.html.twig',
                        [
                            'items' => $this->normalizeItems($this->buildStackWithCurrentRoute()),
                            'options' => $options,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }

    public function breadcrumbAppendRoute(string $route, array $params = [], ?string $label = null): void
    {
        $request = $this->getCurrentRequest();
        if (! $request) {
            return;
        }

        $stack = $this->getStack($request);
        $stack[] = [
            'route' => $route,
            'params' => $params,
            'label' => $label,
        ];

        $request->attributes->set(self::STACK_ATTRIBUTE, $stack);
    }

    public function breadcrumbStack(): array
    {
        $request = $this->getCurrentRequest();
        if (! $request) {
            return [];
        }

        return $this->buildStackWithCurrentRoute();
    }

    private function buildStackWithCurrentRoute(): array
    {
        $request = $this->getCurrentRequest();
        if (! $request) {
            return [];
        }

        $stack = $this->getStack($request);
        $currentRoute = $request->attributes->get('_route');

        if ($currentRoute) {
            $last = end($stack);
            $lastRoute = is_array($last) ? ($last['route'] ?? null) : null;

            if ($lastRoute !== $currentRoute) {
                $stack[] = [
                    'route' => $currentRoute,
                    'params' => $request->attributes->get('_route_params', []),
                ];
            }
        }

        return $stack;
    }

    private function getStack(Request $request): array
    {
        $stack = $request->attributes->get(self::STACK_ATTRIBUTE, []);

        return is_array($stack) ? $stack : [];
    }

    private function getCurrentRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $normalized[] = [
                    'route' => $item,
                    'params' => [],
                ];
                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $normalized[] = [
                'route' => $item['route'] ?? null,
                'params' => $item['params'] ?? [],
                'label' => $item['label'] ?? null,
                'current' => $item['current'] ?? false,
            ];
        }

        return $normalized;
    }
}
