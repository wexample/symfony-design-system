<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Twig\Environment;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Interface\DevMenuProviderInterface;
use Wexample\SymfonyLoader\Twig\ComponentsExtension;

/**
 * The « dev » marker of the footer, and the menu it opens: what the installed
 * bundles offer to whoever develops — none of it there in production.
 */
class DevMenuExtension extends AbstractTemplateExtension
{
    /**
     * @param iterable<DevMenuProviderInterface> $providers
     */
    public function __construct(
        ComponentsExtension $componentsExtension,
        #[AutowireIterator(DevMenuProviderInterface::TAG)]
        private readonly iterable $providers,
        #[Autowire(param: 'kernel.environment')]
        private readonly string $environment,
    ) {
        parent::__construct($componentsExtension);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'dev_menu',
                function (Environment $twig, $context): string {
                    if ('prod' === $this->environment) {
                        return '';
                    }

                    // The links first, always there; then, under a rule, what
                    // acts on the account signed in (`account`), unavailable
                    // to whoever is not — so the menu keeps one shape.
                    $links = [];
                    $accountItems = [];

                    foreach ($this->providers as $provider) {
                        foreach ($provider->getDevMenuItems() as $item) {
                            // In the dev tone, as everything development alone shows.
                            $item += ['tone' => 'dev'];

                            if ($item['account'] ?? false) {
                                $accountItems[] = $item;
                            } else {
                                $links[] = $item;
                            }
                        }
                    }

                    // Within each part, by `order` (0 by default), then as given.
                    $byOrder = static fn (array $a, array $b): int => ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
                    usort($links, $byOrder);
                    usort($accountItems, $byOrder);

                    $items = $links && $accountItems
                        ? [...$links, ['separator' => true], ...$accountItems]
                        : [...$links, ...$accountItems];

                    return $this->renderComponent(
                        $twig,
                        $context,
                        '@WexampleSymfonyDesignSystemBundle/components/dev-menu',
                        [
                            'environment' => $this->environment,
                            'items' => $items,
                        ]
                    );
                },
                self::TEMPLATE_FUNCTION_OPTIONS
            ),
        ];
    }
}
