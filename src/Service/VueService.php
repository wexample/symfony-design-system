<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Twig\Environment;
use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\Vue;
use Wexample\SymfonyTranslations\Translation\Translator;

class VueService
{
    public array $renderedTemplates = [];

    public array $rootComponents = [];

    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
        protected AssetsService $assetsService,
        protected ComponentService $componentsService,
        protected Translator $translator
    ) {
    }

    public function isRenderPassInVueContext(): bool
    {
        return ComponentService::COMPONENT_NAME_VUE === $this
                ->adaptiveResponseService
                ->renderPass
                ->getCurrentContextRenderNode()->name;
    }

    /**
     * @throws \Exception
     */
    public function vueRender(
        Environment $twig,
        string $path,
        ?array $props = [],
        ?array $twigContext = []
    ): string {
        $vue = new Vue(
            $path,
        );

        $pathTemplate = $vue->findTemplate($twig);

        $renderPass = $this->adaptiveResponseService->renderPass;

        $options = [
            'path' => $vue->path,
            'vueComId' => $vue->id,
            'vueComName' => $vue->name,
            'props' => $props,
        ];

        $outputBody = '';

        if (!$this->isRenderPassInVueContext()) {
            $rootComponent = $this
                ->componentsService
                ->registerComponent(
                    $twig,
                    ComponentService::COMPONENT_NAME_VUE,
                    ComponentService::INIT_MODE_PARENT,
                    $options
                );

            $this->rootComponents[$vue->name] = $rootComponent;

            $outputBody = $rootComponent->renderTag();
        } else {
            $rootComponent = $renderPass->getCurrentContextRenderNode();

            $contextCurrent = RenderingHelper::buildRenderContextKey(
                RenderingHelper::CONTEXT_COMPONENT,
                ComponentService::COMPONENT_NAME_VUE
            );

            if ($rootComponent->getContextRenderNodeKey() !== $contextCurrent) {
                throw new \Exception('Trying to render a non-root vue outside the vue context. Current context is '.$contextCurrent);
            }
        }

        // Append assets to root vue component.
        $this
            ->assetsService
            ->assetsDetect(
                $vue->path,
                $rootComponent,
                $rootComponent->assets
            );

        if (!isset($this->renderedTemplates[$vue->name])) {
            $renderPass->setCurrentContextRenderNode(
                $rootComponent
            );

            $this->translator->setDomainFromPath(
                Translator::DOMAIN_TYPE_VUE,
                $vue->path
            );

            $template = DomHelper::buildTag(
                'template',
                [
                    'class' => 'vue vue-loading',
                    'id' => 'vue-template-'.$vue->name,
                ],
                $twig->render(
                    $pathTemplate,
                    $twigContext + $options
                )
            );

            $rootComponent->translations['INCLUDE|'.$vue->name] = $this->translator->transFilter('@vue::*');

            $this->translator->revertDomain(
                Translator::DOMAIN_TYPE_VUE
            );

            $renderPass->revertCurrentContextRenderNode();

            $this->renderedTemplates[$vue->name] = $template;
        }

        if ($this->adaptiveResponseService->getResponse()->isJsonRequest()) {
            $renderPass->layoutRenderNode->vueTemplates = $this->renderedTemplates;
        }

        return DomHelper::buildTag(
            $vue->name,
            [
                'class' => $vue->id,
            ],
            $outputBody
        );
    }
}
