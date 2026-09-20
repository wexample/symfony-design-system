<?php

namespace Wexample\SymfonyDesignSystem\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Class\ElementEntry;
use Wexample\SymfonyDesignSystem\Class\ElementInventory;
use Wexample\SymfonyDesignSystem\Class\ElementSource;
use Wexample\SymfonyDesignSystem\Enum\ElementFormat;
use Wexample\SymfonyDesignSystem\Service\ElementRegistryService;
use Wexample\SymfonyDesignSystem\Service\ElementScannerService;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;
use Wexample\SymfonyHelpers\Command\AbstractBundleCommand;
use Wexample\SymfonyHelpers\Service\BundleService;

/**
 * Walks each bundle that signed up as holding elements, and writes its registry.
 *
 * One file per bundle, inside that bundle: a package ships the registry of what
 * it holds. Run it after adding, moving or removing an element. `--check` is the
 * same walk without the writing, for a build that wants to fail on a stale file
 * rather than serve one.
 */
class GenerateElementRegistryCommand extends AbstractBundleCommand
{
    protected static $defaultDescription = 'Writes the registry of design system elements and the formats each is delivered in';

    public function __construct(
        BundleService $bundleService,
        private readonly ElementScannerService $scannerService,
        private readonly ElementRegistryService $registryService,
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

        $this
            ->addOption(
                'check',
                null,
                InputOption::VALUE_NONE,
                'Write nothing and fail when the registry no longer matches the assets'
            )
            ->addOption(
                'table',
                null,
                InputOption::VALUE_NONE,
                'Print the elements as a table, for reading rather than for machines'
            )
            ->addOption(
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
        $sources = $this->resolveSources($input->getOption('source'));

        if ($sources === []) {
            $io->warning(
                'No bundle declares itself a holder of design system elements. '
                . 'A bundle joins by implementing DesignSystemElementsBundleInterface.'
            );

            return self::SUCCESS;
        }

        $stale = [];

        foreach ($sources as $source) {
            $io->section($source->alias);

            $inventory = $this->scannerService->scan($this->twig, $source);

            if ($input->getOption('table')) {
                $this->writeTable($io, $inventory->getEntries());
            }

            $this->writeSummary($io, $inventory);

            if ($input->getOption('check')) {
                if (! $this->registryService->isUpToDate($source, $inventory)) {
                    $stale[] = $source->alias;
                }

                continue;
            }

            $path = $this->registryService->getPath($source);

            $io->writeln(
                $this->registryService->write($source, $inventory)
                    ? 'Written: ' . $path
                    : 'Unchanged: ' . $path
            );
        }

        if (! $input->getOption('check')) {
            return self::SUCCESS;
        }

        if ($stale === []) {
            $io->success('Every registry matches the assets.');

            return self::SUCCESS;
        }

        $io->error(
            'Out of date: ' . implode(', ', $stale) . '. Run '
            . self::buildDefaultName() . ' and commit the result.'
        );

        return self::FAILURE;
    }

    /**
     * @return ElementSource[]
     */
    private function resolveSources(?string $alias): array
    {
        if ($alias === null) {
            return $this->scannerService->getSources();
        }

        $source = $this->scannerService->getSource($alias);

        return $source === null ? [] : [$source];
    }

    /**
     * @param ElementEntry[] $entries
     */
    private function writeTable(
        SymfonyStyle $io,
        array $entries
    ): void {
        $formats = ElementFormat::cases();

        $io->table(
            array_merge(
                ['element'],
                array_map(
                    static fn (ElementFormat $format): string => $format->value,
                    $formats
                )
            ),
            array_map(
                static fn (ElementEntry $entry): array => array_merge(
                    [$entry->key],
                    array_map(
                        static fn (ElementFormat $format): string => $entry->has($format) ? 'x' : '',
                        $formats
                    )
                ),
                $entries
            )
        );
    }

    private function writeSummary(
        SymfonyStyle $io,
        ElementInventory $inventory
    ): void {
        $io->writeln(
            sprintf(
                '%d elements, %d of them in a single format.',
                $inventory->countEntries(),
                $inventory->countByFormatsCount()[1]
            )
        );

        $io->comment('Not scanned: ' . implode(', ', $inventory->getUnscannedPaths()));
    }
}
