<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Helper\DomHelper;
use Wexample\SymfonyDesignSystem\Rendering\ComponentManagerLocatorService;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\ComponentRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Helper\VariableHelper;
use Wexample\SymfonyTranslations\Translation\Translator;

class ComponentService extends RenderNodeService
{
    // Component is loaded with a css class.
    public const INIT_MODE_CLASS = VariableHelper::CLASS_VAR;

    // Component is loaded from template into the target tag.
    public const INIT_MODE_PARENT = VariableHelper::PARENT;

    // Component is loaded from template, just after target tag.
    public const INIT_MODE_PREVIOUS = VariableHelper::PREVIOUS;

    public const COMPONENT_NAME_VUE = 'components/vue';

    public function __construct(
        AssetsService $assetsService,
        readonly protected ComponentManagerLocatorService $componentManagerLocatorService,
        readonly protected Translator $translator
    ) {
        parent::__construct(
            $assetsService,
        );
    }

    /**
     * @throws Exception
     */
    public function componentRenderBody(
        RenderPass $renderPass,
        Environment $env,
        ComponentRenderNode $component
    ): string {
        $loader = $env->getLoader();

        try {
            if ($loader->exists($component->getTemplatePath())) {
                $renderPass->setCurrentContextRenderNode(
                    $component
                );

                $this->translator->setDomainFromPath(
                    Translator::DOMAIN_TYPE_COMPONENT,
                    $component->getTemplateAbstractPath()
                );

                $component->render(
                    $env,
                );

                $this->translator->revertDomain(
                    Translator::DOMAIN_TYPE_COMPONENT
                );

                $renderPass->revertCurrentContextRenderNode();
            } else {
                $component->setBody(null);
            }

            return DomHelper::buildTag(DomHelper::TAG_SPAN);
        } catch (Exception $exception) {
            throw new Exception('Error during rendering component '.$component->getTemplateAbstractPath().' : '.$exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Init a components and provide a class name to retrieve dom element.
     *
     * @throws Exception
     */
    public function componentInitClass(
        Environment $twig,
        RenderPass $renderPass,
        string $name,
        array $options = []
    ): ComponentRenderNode {
        return $this->registerComponent(
            $twig,
            $renderPass,
            $name,
            self::INIT_MODE_CLASS,
            $options
        );
    }

    /**
     * Add component to the global page requirements.
     * It adds components assets to page assets.
     *
     * @throws Exception
     */
    public function componentInitParent(
        Environment $twig,
        RenderPass $renderPass,
        string $name,
        array $options = []
    ): ComponentRenderNode {
        return $this->registerComponent(
            $twig,
            $renderPass,
            $name,
            self::INIT_MODE_PARENT,
            $options
        );
    }

    /**
     * @throws Exception
     */
    public function componentInitPrevious(
        Environment $twig,
        RenderPass $renderPass,
        string $name,
        array $options = []
    ): ComponentRenderNode {
        return $this->registerComponent(
            $twig,
            $renderPass,
            $name,
            self::INIT_MODE_PREVIOUS,
            $options
        );
    }

    /**
     * @throws Exception
     */
    public function registerComponent(
        Environment $twig,
        RenderPass $renderPass,
        string $name,
        string $initMode,
        array $options = [],
    ): ComponentRenderNode {
        $componentManager = $this
            ->componentManagerLocatorService
            ->getComponentService($name);

        $component = $componentManager?->createComponent(
            $initMode,
            $options
        );

        if (!$component) {
            $component = new ComponentRenderNode(
                $initMode,
                $options
            );
        }

        $this->initRenderNode(
            $component,
            $renderPass,
            $name,
        );

        $this->componentRenderBody(
            $renderPass,
            $twig,
            $component
        );

        return $component;
    }
}
