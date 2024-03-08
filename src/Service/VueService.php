<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Helper\RenderingHelper;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Rendering\Vue;
use Wexample\SymfonyDesignSystem\Twig\VueExtension;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;
use Wexample\SymfonyHelpers\Helper\BundleHelper;
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
        return ComponentService::COMPONENT_NAME_VUE === $renderPass->getCurrentContextRenderNode()->getName();
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
            $this->assetsService->buildTemplateNameFromPath($path),
        );

        $pathWithExtension = $path.VueExtension::TEMPLATE_FILE_EXTENSION;

        if (!$twig->getLoader()->exists($pathWithExtension)) {
            throw new Exception('Unable to find template: '.$pathWithExtension);
        }

        $vueTemplateName = $vue->getTemplateAbstractPath();

        $options = [
            'domId' => $vue->domId,
            'name' => $vueTemplateName,
        ];

        $outputBody = '';
        if (!$this->isRenderPassInVueContext($renderPass)) {
            $rootComponent = $this
                ->componentsService
                ->registerComponent(
                    $twig,
                    $renderPass,
                    BundleHelper::ALIAS_PREFIX.WexampleSymfonyDesignSystemBundle::getAlias().'/'.ComponentService::COMPONENT_NAME_VUE,
                    ComponentService::INIT_MODE_PARENT,
                    $options
                );

            $this->rootComponents[$vueTemplateName] = $rootComponent;

            $outputBody = $rootComponent->renderTag();
        } else {
            $rootComponent = $renderPass->getCurrentContextRenderNode();

            $contextCurrent = RenderingHelper::buildRenderContextKey(
                RenderingHelper::CONTEXT_COMPONENT,
                ComponentService::COMPONENT_NAME_VUE
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
                $vueTemplateName
            );

        if (!isset($this->renderedTemplates[$vueTemplateName])) {
            $renderPass->setCurrentContextRenderNode(
                $rootComponent
            );

            $this->translator->setDomainFromPath(
                Translator::DOMAIN_TYPE_VUE,
                $vueTemplateName
            );

            $template = DomHelper::buildTag(
                'template',
                [
                    'class' => 'vue vue-loading',
                    'id' => 'vue-template-'.$vue->domId,
                ],
                $twig->render(
                    $pathWithExtension,
                    $twigContext + $options
                )
            );

            $rootComponent->translations['INCLUDE|'.$vueTemplateName] = $this->translator->transFilter('@vue::*');

            $this->translator->revertDomain(
                Translator::DOMAIN_TYPE_VUE
            );

            $renderPass->revertCurrentContextRenderNode();

            $this->renderedTemplates[$vueTemplateName] = $template;
        }

        return DomHelper::buildTag(
            $vueTemplateName,
            [
                'class' => $vue->domId,
            ],
            $outputBody
        );
    }
}
