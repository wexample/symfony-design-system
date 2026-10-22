<?php

namespace Wexample\SymfonyDesignSystem\Twig;

use Twig\TwigFunction;
use Wexample\SymfonyDesignSystem\Chat\SlashCommand\SlashCommandRegistry;
use Wexample\SymfonyHelpers\Twig\AbstractExtension;
use Wexample\SymfonyLoader\Rendering\RenderPass;

class SlashCommandExtension extends AbstractExtension
{
    public function __construct(
        private readonly SlashCommandRegistry $registry
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'slash_commands_js',
                [
                    $this,
                    'slashCommandsJs',
                ]
            ),
        ];
    }

    /**
     * Hands the commands of a group to the vue being rendered, as a prop.
     *
     * Called from the vue_config block of a chat, the same way translations are
     * handed over: the list is known when the page is built, so it costs the
     * browser no request.
     */
    public function slashCommandsJs(
        RenderPass $renderPass,
        string $group
    ): void {
        $node = $renderPass->getCurrentContextRenderNode();

        $node->options['props']['slashCommandGroup'] = $group;
        $node->options['props']['slashCommands'] = $this->registry->getDescriptors($group);
    }
}
