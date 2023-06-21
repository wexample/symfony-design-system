<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Helper\TemplateHelper;
use Wexample\SymfonyDesignSystem\Rendering\ComponentRenderNodeManager;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\ComponentRenderNode;
use Wexample\SymfonyDesignSystem\Translation\Translator;
use Wexample\SymfonyHelpers\Helper\BundleHelper;
use Wexample\SymfonyHelpers\Helper\ClassHelper;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\TextHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

class ComponentService extends RenderNodeService
{
    // Component is loaded with a css class.
    public const INIT_MODE_CLASS = VariableHelper::CLASS_VAR;

    // Component is simply loaded from PHP or from backend adaptive event.
    // It may have no target tag.
    public const INIT_MODE_LAYOUT = VariableHelper::LAYOUT;

    // Component is loaded from template into the target tag.
    public const INIT_MODE_PARENT = VariableHelper::PARENT;

    // Component is loaded from template, just after target tag.
    public const INIT_MODE_PREVIOUS = VariableHelper::PREVIOUS;

    public const COMPONENT_NAME_VUE = 'components/vue';

    public const COMPONENT_NAME_MODAL = 'components/modal';

    protected array $componentsClasses = [];

    protected array $componentsManagers = [];

    public function __construct(
        protected AdaptiveResponseService $adaptiveResponseService,
        protected AssetsService $assetsService,
        KernelInterface $kernel,
        protected Translator $translator
    ) {
        parent::__construct(
            $assetsService,
            $adaptiveResponseService
        );

        $adaptiveResponseService->addRenderEventListener($this);

        $this->componentsClasses = [];

        $locations = [
            // May be rewritten..
            'vendor/wexample/symfony-design-system/src/' => 'Wexample\\SymfonyDesignSystem\\',
            BundleHelper::DIR_SRC => BundleHelper::CLASS_PATH_PREFIX,
        ];

        foreach ($locations as $location => $classPrefix)
        {
            $locationAbsolute = $kernel->getProjectDir().'/'.$location;

            $this->componentsClasses = $this->findComponentClassesInDir(
                $locationAbsolute,
                $classPrefix,
                'Rendering/Component'
            );

            $managers = $this->findComponentClassesInDir(
                $locationAbsolute,
                $classPrefix,
                'Rendering/ComponentManager',
                'RenderNodeManager'
            );

            foreach ($managers as $componentName => $managerClassName)
            {
                $this->componentsManagers[VariableHelper::PLURAL_COMPONENT.'/'.$componentName] = new $managerClassName(
                    $kernel,
                    $this->adaptiveResponseService
                );
            }
        }
    }

    protected function findComponentClassesInDir(
        string $location,
        string $classPrefix,
        string $classesSubDir,
        string $unwantedSuffix = '',
    ): array {
        $output = [];
        $componentDir = $location.$classesSubDir;

        if (is_dir($componentDir))
        {
            $componentClasses = scandir($componentDir);

            foreach ($componentClasses as $componentClass)
            {
                if ($componentClass[0] !== '.')
                {
                    $componentClassRealPath = $componentDir.FileHelper::FOLDER_SEPARATOR.$componentClass;

                    if (is_file($componentClassRealPath))
                    {
                        $componentClass = FileHelper::removeExtension(
                            $componentClass,
                            FileHelper::FILE_EXTENSION_PHP
                        );

                        $output[TextHelper::toKebab(substr($componentClass, 0, -strlen($unwantedSuffix)))] =
                            $classPrefix.ClassHelper::buildClassNameFromPath(
                                $classesSubDir.'/'.$componentClass
                            );
                    }
                    else
                    {
                        $output += $this->findComponentClassesInDir(
                            $location,
                            $classPrefix,
                            $classesSubDir.'/'.$componentClass,
                        );
                    }
                }
            }
        }

        return $output;
    }

    /**
     * @throws Exception
     */
    public function componentRenderBody(
        Environment $env,
        ComponentRenderNode $component
    ): ?string {
        $loader = $env->getLoader();
        $renderPass = $this->adaptiveResponseService->renderPass;
        $search = TemplateHelper::buildTemplateInheritanceStack(
            $component->name
        );

        try
        {
            foreach ($search as $templatePath)
            {
                if ($loader->exists($templatePath))
                {
                    $renderPass->setCurrentContextRenderNode(
                        $component
                    );

                    $this->translator->setDomainFromPath(
                        Translator::DOMAIN_TYPE_COMPONENT,
                        $component->name
                    );

                    $rendered = $env->render(
                        $templatePath,
                        $component->options
                    );

                    $this->translator->revertDomain(
                        Translator::DOMAIN_TYPE_COMPONENT
                    );

                    $renderPass->revertCurrentContextRenderNode();

                    return $rendered;
                }
            }

            return null;
        }
        catch (Exception $exception)
        {
            throw new Exception('Error during rendering component '.$component->name.' : '.$exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Init a components and provide a class name to retrieve dom element.
     *
     * @throws Exception
     */
    public function componentInitClass(
        Environment $twig,
        string $name,
        array $options = []
    ): ComponentRenderNode {
        return $this->registerComponent(
            $twig,
            $name,
            self::INIT_MODE_CLASS,
            $options
        );
    }

    /**
     * @throws Exception
     */
    public function componentInitLayout(
        Environment $twig,
        string $name,
        array $options = []
    ): ComponentRenderNode {
        $component = $this->registerComponent(
            $twig,
            $name,
            ComponentService::INIT_MODE_LAYOUT,
            $options
        );

        $component->body .= $component->renderTag();

        return $component;
    }

    /**
     * Add component to the global page requirements.
     * It adds components assets to page assets.
     *
     * @throws Exception
     */
    public function componentInitParent(
        Environment $twig,
        string $name,
        array $options = []
    ): ComponentRenderNode {
        return $this->registerComponent(
            $twig,
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
        string $name,
        array $options = []
    ): ComponentRenderNode {
        return $this->registerComponent(
            $twig,
            $name,
            self::INIT_MODE_PREVIOUS,
            $options
        );
    }

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
        string $name,
        string $initMode,
        array $options = [],
    ): ComponentRenderNode {
        $className = $this->findComponentClassName($name);

        // Using an object allow continuing edit properties after save.
        /** @var ComponentRenderNode $component */
        $component = new $className(
            $this->adaptiveResponseService->renderPass,
            $initMode,
            $options
        );

        $this->getComponentManager($name)
            ?->createComponent($component);

        $this->initRenderNode(
            $component,
            $name,
            $this->adaptiveResponseService->renderPass->useJs,
        );

        $component->body = $this->componentRenderBody(
            $twig,
            $component
        );

        return $component;
    }

    public function renderEventPostRender(array &$options)
    {
        /** @var ComponentRenderNodeManager $componentsManager */
        foreach ($this->componentsManagers as $componentsManager)
        {
            $componentsManager->postRender();
        }
    }
}
