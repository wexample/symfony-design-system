<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Rendering\ComponentRenderNodeManager;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\ComponentRenderNode;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

class ComponentService extends RenderNodeService
{

    // Component is loaded from template into the target tag.
    public const INIT_MODE_PARENT = VariableHelper::PARENT;

    /**
     * @throws Exception
     */
    public function componentRenderBody(
        RenderPass $renderPass,
        Environment $env,
        ComponentRenderNode $component
    ): ?string {
        return null;
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


    public function findComponentClassName(string $name): string
    {
        return $this->componentsClasses[$name] ?? ComponentRenderNode::class;
    }

    public function getComponentManager(string $name): ?ComponentRenderNodeManager
    {
        return $this->componentsManagers[$name] ?? null;
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
        $className = $this->findComponentClassName($name);

        /** @var ComponentRenderNode $component */
        $component = new $className(
            $renderPass,
            $initMode,
            $options
        );

        $this->getComponentManager($name)
            ?->createComponent($component);

        $this->initRenderNode(
            $renderPass,
            $component,
            $name,
        );

        $component->body = $this->componentRenderBody(
            $renderPass,
            $twig,
            $component
        );

        return $component;
    }
}
