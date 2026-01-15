<?php

namespace Wexample\SymfonyDesignSystem\Twig;

class ButtonExtension extends AbstractTemplateExtension
{
    protected function getTemplateMap(): array
    {
        return [
            'button' => '@WexampleSymfonyDesignSystemBundle/components/button.html.twig',
        ];
    }
}
