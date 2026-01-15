<?php

namespace Wexample\SymfonyDesignSystem\Twig;

class ButtonExtension extends AbstractTemplateExtension
{
    protected function getFunctionName(): string
    {
        return 'button';
    }

    protected function getTemplatePath(): string
    {
        return '@WexampleSymfonyDesignSystemBundle/components/button.html.twig';
    }
}
