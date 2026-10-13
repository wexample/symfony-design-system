<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Service\AvatarService;

/**
 * A face drawn for whoever has no picture, in the round `avatar` frame:
 *
 *     {{ avatar(user.id, { label: user.displayName }) }}
 *     {{ avatar(user.id, { engine: 'wexies-2020' }) }}
 *
 * The seed is what does not change — an id —, never the name: a face that
 * changed with a rename would be someone else's. Its size is the frame's,
 * `--avatar-size`.
 */
class AvatarExtension extends AbstractExtension
{
    public function __construct(
        private readonly AvatarService $avatars,
        #[Autowire(param: 'wexample_symfony_design_system.avatar.default_engine')]
        private readonly string $defaultEngine,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('avatar', $this->avatar(...), ['is_safe' => ['html']]),
            new TwigFunction('avatar_url', $this->avatarUrl(...)),
        ];
    }

    public function avatar(
        string $seed,
        array $options = []
    ): string {
        $label = $options['label'] ?? null;

        return sprintf(
            '<span class="avatar avatar--generated%s"%s><img src="%s" alt="%s" loading="lazy"></span>',
            empty($options['class']) ? '' : ' '.htmlspecialchars($options['class']),
            empty($options['size']) ? '' : sprintf(' style="--avatar-size: %s"', htmlspecialchars($options['size'])),
            htmlspecialchars($this->avatarUrl($seed, $options)),
            htmlspecialchars((string) ($options['alt'] ?? $label ?? ''))
        );
    }

    public function avatarUrl(
        string $seed,
        array $options = []
    ): string {
        return $this->avatars->url($options['engine'] ?? $this->defaultEngine, $seed, $options['label'] ?? null);
    }
}
