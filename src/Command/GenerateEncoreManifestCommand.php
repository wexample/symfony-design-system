<?php

namespace Wexample\SymfonyDesignSystem\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Wexample\SymfonyDesignSystem\Service\Encore\EncoreManifestBuilder;
use Wexample\SymfonyDesignSystem\Traits\SymfonyDesignSystemBundleClassTrait;
use Wexample\SymfonyHelpers\Command\AbstractBundleCommand;
use Wexample\SymfonyHelpers\Helper\JsonHelper;
use Wexample\SymfonyHelpers\Service\BundleService;

class GenerateEncoreManifestCommand extends AbstractBundleCommand
{
    use SymfonyDesignSystemBundleClassTrait;

    private const OPTION_OUTPUT = 'output';
    private const OPTION_PRETTY = 'pretty';
    private const DEFAULT_FILENAME = 'assets/encore.manifest.json';

    public function __construct(
        BundleService $bundleService,
        private readonly EncoreManifestBuilder $manifestBuilder,
        private readonly KernelInterface $kernel,
        private readonly Filesystem $filesystem,
        string $name = null,
    ) {
        parent::__construct(
            $bundleService,
            $name
        );
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription('Build the Encore manifest that lists every discoverable front asset.')
            ->addOption(
                self::OPTION_OUTPUT,
                'o',
                InputOption::VALUE_OPTIONAL,
                'Path (relative to the project directory) where the manifest should be written',
                self::DEFAULT_FILENAME
            )
            ->addOption(
                self::OPTION_PRETTY,
                null,
                InputOption::VALUE_NEGATABLE,
                'Pretty print the generated JSON manifest',
                true
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        return $this->executeAndCatchErrors(
            $input,
            $output,
            function (
                InputInterface $input,
                OutputInterface $output,
                SymfonyStyle $io
            ): int {
                $manifest = $this->manifestBuilder->build();

                $targetPath = $this->resolveOutputPath(
                    (string) $input->getOption(self::OPTION_OUTPUT)
                );
                $this->filesystem->mkdir(\dirname($targetPath));

                $flags = $input->getOption(self::OPTION_PRETTY)
                    ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                    : 0;

                if (!JsonHelper::write(
                    $targetPath,
                    $manifest,
                    $flags
                )) {
                    throw new RuntimeException(sprintf('Unable to write Encore manifest to %s', $targetPath));
                }

                $io->success(sprintf(
                    'Encore manifest written to %s (%d front%s, version %s)',
                    $this->formatDisplayPath($targetPath),
                    $manifest['frontCount'] ?? count($manifest['fronts']),
                    (($manifest['frontCount'] ?? 0) === 1 ? '' : 's'),
                    $manifest['version'] ?? '?'
                ));

                return Command::SUCCESS;
            }
        );
    }

    private function resolveOutputPath(?string $option): string
    {
        $filename = $option ?: self::DEFAULT_FILENAME;

        if ($this->isAbsolutePath($filename)) {
            return $filename;
        }

        return rtrim($this->kernel->getProjectDir(), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .ltrim($filename, DIRECTORY_SEPARATOR.'./');
    }

    private function formatDisplayPath(string $absolutePath): string
    {
        $projectDir = rtrim($this->kernel->getProjectDir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (str_starts_with($absolutePath, $projectDir)) {
            return './'.ltrim(substr($absolutePath, strlen($projectDir)), DIRECTORY_SEPARATOR);
        }

        return $absolutePath;
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || str_starts_with($path, '\\\\')
            || (bool) preg_match('#^[a-zA-Z]:\\\\#', $path);
    }
}
