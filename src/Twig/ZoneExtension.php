<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\UiStateService;

/**
 * What a region of a layout carries of what the visitor did to it.
 *
 * A zone is markup a page writes itself — a div with the classes it needs —
 * so the memory cannot live in a component the way a fold does. It is written
 * here instead, as the two attributes a remembered region wears: the name it
 * is known by, and the size it was left at, read back before the page is sent
 * so it arrives at that size rather than jumping to it.
 */
class ZoneExtension extends AbstractExtension
{
    private const KEY_PREFIX = 'ui.layout.zone.';

    public function __construct(
        private readonly UiStateService $uiState,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'zone_state',
                [$this, 'zoneState'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function zoneState(string $id): string
    {
        $attributes = 'data-zone-id="'.htmlspecialchars($id, ENT_QUOTES).'"';
        $size = $this->uiState->get(self::KEY_PREFIX.$id.'.size');

        if (is_numeric($size)) {
            $attributes .= ' style="--zone-size: '.((int) $size).'px"';
        }

        return $attributes;
    }
}
