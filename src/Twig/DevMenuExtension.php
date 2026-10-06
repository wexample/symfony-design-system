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

                    $items = [];

                    foreach ($this->providers as $provider) {
                        foreach ($provider->getDevMenuItems() as $item) {
                            // In the dev tone, as everything development alone shows.
                            $items[] = $item + ['tone' => 'dev'];
                        }
                    }

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
