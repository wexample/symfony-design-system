<?php

namespace Wexample\SymfonyDesignSystem\Rendering\RenderNode;

use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\AssetsService;
use Wexample\SymfonyTranslations\Translation\Translator;

class InitialLayoutRenderNode extends AbstractLayoutRenderNode
{
    public string $colorScheme;

    public string $translationsDomainSeparator = Translator::DOMAIN_SEPARATOR;

    public function __construct(
        protected RenderPass $renderPass,
        public bool $useJs,
        protected string $env
    ) {
        parent::__construct(
            $renderPass,
            $useJs
        );
    }

    public function init(
        RenderPass $renderPass,
        string $name,
    ):void {
        parent::init(
            $renderPass,
            $name
        );

        $this->page->isInitialPage = true;

        $this->vars += [
            'colorScheme' => $this->colorScheme,
            'displayBreakpoints' => AssetsService::DISPLAY_BREAKPOINTS,
            'translationsDomainSeparator' => $this->translationsDomainSeparator,
            'useJs' => $this->useJs,
        ];
    }
}
