<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Rendering\Vue;
use Wexample\SymfonyDesignSystem\Twig\VueExtension;
use Wexample\SymfonyTranslations\Translation\Translator;

class VueService
{
    public array $renderedTemplates = [];

    public array $rootComponents = [];

    public function __construct(
        readonly protected AdaptiveResponseService $adaptiveResponseService,
        readonly protected AssetsService $assetsService,
        readonly protected ComponentService $componentsService,
        readonly protected Translator $translator
    ) {
    }

    public function isRenderPassInVueContext(RenderPass $renderPass): bool
    {
        return ComponentService::COMPONENT_NAME_VUE === $renderPass->getCurrentContextRenderNode()->getTemplateAbstractPath();
    }

    /**
     * @throws Exception
     */
    public function vueRender(
        Environment $twig,
        RenderPass $renderPass,
        string $path,
        ?array $props = [],
        ?array $twigContext = []
    ): string {
        $vue = new Vue(
            $this->assetsService->buildTemplateAbstractPathFromTemplateName($path),
        );

        $pathWithExtension = $path.VueExtension::TEMPLATE_FILE_EXTENSION;

        if (!$twig->getLoader()->exists($pathWithExtension)) {
            throw new Exception('Unable to find template: '.$pathWithExtension);
        }

        $vueTemplateAbstractPath = $vue->getTemplateAbstractPath();
        $vueDomId = $vue->getDomId();

        $options = [
            'domId' => $vueDomId,
            'name' => $vueTemplateAbstractPath,
        ];

        $outputBody = '';
        $componentName = ComponentService::buildCoreComponentName(ComponentService::COMPONENT_NAME_VUE);

        if (!$this->isRenderPassInVueContext($renderPass)) {
            $rootComponent = $this
                ->componentsService
                ->registerComponent(
                    $twig,
                    $renderPass,
                    $componentName,
                    ComponentService::INIT_MODE_PARENT,
                    $options
                );

            $this->rootComponents[$vueTemplateAbstractPath] = $rootComponent;

            $outputBody = $rootComponent->renderTag();
        } else {
            $rootComponent = $renderPass->getCurrentContextRenderNode();

            $contextCurrent = RenderingHelper::buildRenderContextKey(
                RenderingHelper::CONTEXT_COMPONENT,
                $componentName
            );

            if ($rootComponent->getContextRenderNodeKey() !== $contextCurrent) {
                throw new Exception('Trying to render a non-root vue outside the vue context. Current context is '.$contextCurrent);
            }
        }

        // Append assets to root vue component.
        $this
            ->assetsService
            ->assetsDetect(
                $renderPass,
                $rootComponent,
                $vueTemplateAbstractPath
            );

        if (!isset($this->renderedTemplates[$vueTemplateAbstractPath])) {
            $renderPass->setCurrentContextRenderNode(
                $rootComponent
            );

            $this->translator->setDomainFromPath(
                Translator::DOMAIN_TYPE_VUE,
                $vueTemplateAbstractPath
            );

            $template = DomHelper::buildTag(
                'template',
                [
                    'class' => 'vue vue-loading',
                    'id' => 'vue-template-'.$vueDomId,
                ],
                $twig->render(
                    $pathWithExtension,
                    $twigContext + $options + ['render_pass' => $renderPass]
                )
            );

            $rootComponent->translations['INCLUDE|'.$vueTemplateAbstractPath] = $this->translator->transFilter('@vue::*');

            $this->translator->revertDomain(
                Translator::DOMAIN_TYPE_VUE
            );

            $renderPass->revertCurrentContextRenderNode();

            $this->renderedTemplates[$vueTemplateAbstractPath] = $template;
        }

        if ($renderPass->isJsonRequest()) {
            $renderPass->layoutRenderNode->vueTemplates = $this->renderedTemplates;
        }

        return DomHelper::buildTag(
            $vueTemplateAbstractPath,
            [
                'class' => $vueDomId,
            ],
            $outputBody
        );
    }
}
