<?php

namespace Wexample\SymfonyDesignSystem\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Helper\DesignSystemHelper;
use Wexample\Helpers\Helper\PathHelper;
use Wexample\SymfonyDesignSystem\Traits\SymfonyDesignSystemBundleClassTrait;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;
use Wexample\SymfonyHelpers\Command\AbstractBundleCommand;
use Wexample\SymfonyHelpers\Helper\FileHelper;
use Wexample\SymfonyHelpers\Helper\JsonHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;
use Wexample\SymfonyHelpers\Service\BundleService;

class GetFrontsCommand extends AbstractBundleCommand
{
    use SymfonyDesignSystemBundleClassTrait;

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly ParameterBagInterface $parameterBag,
        BundleService $bundleService,
        string $name = null,
    ) {
        parent::__construct(
            $bundleService,
            $name
        );
    }

    public static function getBundleClassName(): string
    {
        return WexampleSymfonyDesignSystemBundle::class;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        if ($this->buildFrontsPathsList()) {
            $io->success(
                'Created fronts folders list in '
                .$this->getFrontsListPath()
            );

            return Command::SUCCESS;
        }

        $io->error('Unexpected error');

        return Command::FAILURE;
    }

    private function buildFrontsPathsList(): bool
    {
        return JsonHelper::write(
            $this->getFrontsListPath(),
            $this->getFrontPaths(),
            JSON_PRETTY_PRINT
        );
    }

    private function getFrontsListPath(): string
    {
        return PathHelper::join([
            $this->kernel->getProjectDir(),
            VariableHelper::ASSETS,
            DesignSystemHelper::TWIG_NAMESPACE_FRONT
            .FileHelper::EXTENSION_SEPARATOR
            .VariableHelper::JSON,
        ]);
    }

    private function getFrontPaths(): array
    {
        $pathsGroups = $this->parameterBag->get('design_system_packages_front_paths');
        $rootLen = strlen($this->kernel->getProjectDir().FileHelper::FOLDER_SEPARATOR);

        $paths = [];
        foreach ($pathsGroups as $group) {
            foreach ($group as $key => $path) {
                // Return relative to project root.
                $paths[$key] = './'.substr(
                        $path,
                        $rootLen
                    );
            }
        }

        return $paths;
    }
}
