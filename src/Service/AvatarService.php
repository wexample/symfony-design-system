<?php

namespace Wexample\SymfonyDesignSystem\Service;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wexample\SymfonyDesignSystem\Controller\AvatarController;
use Wexample\SymfonyDesignSystem\Interface\AvatarEngineInterface;

/**
 * The avatar engines the application holds, by name — the design system's
 * own and whatever a bundle or the application adds —, and where the face
 * an engine draws for a seed is served.
 */
class AvatarService
{
    /**
     * @var array<string, AvatarEngineInterface>
     */
    private readonly array $engines;

    /**
     * @param iterable<AvatarEngineInterface> $engines
     */
    public function __construct(
        #[AutowireIterator(AvatarEngineInterface::TAG)]
        iterable $engines,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        $byName = [];
        foreach ($engines as $engine) {
            $byName[$engine::getName()] = $engine;
        }

        $this->engines = $byName;
    }

    /**
     * @return list<string>
     */
    public function getEngineNames(): array
    {
        return array_keys($this->engines);
    }

    public function hasEngine(string $name): bool
    {
        return isset($this->engines[$name]);
    }

    public function render(
        string $engine,
        string $seed,
        ?string $label = null
    ): string {
        if (! $this->hasEngine($engine)) {
            throw new InvalidArgumentException(sprintf('No avatar engine "%s"; there are: %s.', $engine, implode(', ', $this->getEngineNames())));
        }

        return $this->engines[$engine]->render($seed, $label);
    }

    /**
     * Where the face is served, an SVG an `img` shows — in a page, a vue
     * component or a mail alike.
     */
    public function url(
        string $engine,
        string $seed,
        ?string $label = null
    ): string {
        return $this->urlGenerator->generate(
            AvatarController::ROUTE_NAME,
            array_filter(['engine' => $engine, 'seed' => $seed, 'label' => $label], fn (?string $value) => null !== $value && '' !== $value)
        );
    }
}
