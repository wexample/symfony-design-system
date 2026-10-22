<?php

namespace Wexample\SymfonyDesignSystem\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Class\ElementDeclaration;
use Wexample\SymfonyDesignSystem\Class\ElementEntry;
use Wexample\SymfonyDesignSystem\Enum\ElementFormat;
use Wexample\SymfonyDesignSystem\Service\ElementDeclarationService;
use Wexample\SymfonyDesignSystem\Service\ElementScannerService;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;
use Wexample\SymfonyHelpers\Command\AbstractBundleCommand;
use Wexample\SymfonyHelpers\Service\BundleService;

/**
 * Writes a declaration for every element found on disk that has none yet.
 *
 * A hand-written file is never touched: the command exists so that nobody types
 * a skeleton, not so that anything rewrites what a person decided. Run it when a
 * bundle joins the registry, or after adding an element; run again with nothing
 * new, it writes nothing.
 */
class SeedElementsCommand extends AbstractBundleCommand
{
    protected static $defaultDescription = 'Writes a yaml declaration for each design system element that has none yet';

    public function __construct(
        BundleService $bundleService,
        private readonly ElementScannerService $scannerService,
        private readonly ElementDeclarationService $declarationService,
        private readonly Environment $twig,
        ?string $name = null,
    ) {
        parent::__construct($bundleService, $name);
    }

    public static function getBundleClassName(): string
    {
        return WexampleSymfonyDesignSystemBundle::class;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'source',
            null,
            InputOption::VALUE_REQUIRED,
            'Only this bundle alias, instead of every bundle holding elements'
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $alias = $input->getOption('source');

        $sources = $alias === null
            ? $this->scannerService->getSources()
            : array_filter([$this->scannerService->getSource($alias)]);

        if ($sources === []) {
            $io->warning('No bundle declares itself a holder of design system elements.');

            return self::SUCCESS;
        }

        foreach ($sources as $source) {
            $io->section($source->alias);

            $written = 0;
            $kept = 0;

            foreach ($this->scannerService->scan($this->twig, $source)->getEntries() as $entry) {
                if ($this->declarationService->write($source, $this->seed($entry))) {
                    ++$written;
                    $io->writeln('  + ' . $this->declarationService->getPath($source, $entry->key));
                } else {
                    ++$kept;
                }
            }

            $io->writeln(sprintf('%d written, %d already declared and left alone.', $written, $kept));
        }

        return self::SUCCESS;
    }

    /**
     * What the scan can say about the element and nothing more: the formats it
     * was found in are expected, the others are left undecided for a person.
     */
    private function seed(ElementEntry $entry): ElementDeclaration
    {
        $formats = [];

        foreach (ElementFormat::cases() as $format) {
            if ($entry->has($format)) {
                $formats[$format->value] = ElementDeclaration::EXPECTED;
            }
        }

        return new ElementDeclaration($entry->key, null, $formats);
    }
}
