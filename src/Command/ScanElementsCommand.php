<?php

namespace Wexample\SymfonyDesignSystem\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Class\ElementEntry;
use Wexample\SymfonyDesignSystem\Enum\ElementFormat;
use Wexample\SymfonyDesignSystem\Service\ElementScannerService;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;
use Wexample\SymfonyHelpers\Command\AbstractBundleCommand;
use Wexample\SymfonyHelpers\Service\BundleService;

/**
 * Prints what elements the design system holds and in which formats.
 *
 * The same answer the inventory page shows, from a shell and as json, so that
 * the day the scan becomes a declaration the difference between the two can be
 * diffed rather than argued about.
 */
class ScanElementsCommand extends AbstractBundleCommand
{
    protected static $defaultDescription = 'Lists the design system elements and the formats each is delivered in';

    public function __construct(
        BundleService $bundleService,
        private readonly ElementScannerService $scannerService,
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
            'json',
            null,
            InputOption::VALUE_NONE,
            'Print the inventory as json instead of a table'
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $inventory = $this->scannerService->scan($this->twig);

        if ($input->getOption('json')) {
            $output->writeln(
                json_encode(
                    $inventory->toArray(),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                )
            );

            return self::SUCCESS;
        }

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
                $inventory->getEntries()
            )
        );

        $io->writeln(
            sprintf(
                '%d elements, %d of them in a single format.',
                $inventory->countEntries(),
                $inventory->countByFormatsCount()[1]
            )
        );

        $io->comment('Not scanned: ' . implode(', ', $inventory->getUnscannedPaths()));

        return self::SUCCESS;
    }
}
