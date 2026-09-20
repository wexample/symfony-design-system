<?php

namespace Wexample\SymfonyDesignSystem\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;
use Wexample\SymfonyDesignSystem\Class\ElementEntry;
use Wexample\SymfonyDesignSystem\Class\ElementSource;
use Wexample\SymfonyDesignSystem\Enum\ElementFormat;
use Wexample\SymfonyDesignSystem\Service\ElementRegistryService;
use Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle;
use Wexample\SymfonyHelpers\Command\AbstractBundleCommand;
use Wexample\SymfonyHelpers\Service\BundleService;

/**
 * Compiles each bundle's declarations against its assets and writes its registry.
 *
 * Nothing is written for a bundle whose declarations and disk disagree: the
 * command lists every point of disagreement and fails, since a registry built
 * on one would only be read by something that trusts it. `--check` is the same
 * run without the writing, plus a failure when the file on disk is not what
 * would be written — for a build that wants to catch a declaration edited and
 * not regenerated.
 */
class GenerateElementRegistryCommand extends AbstractBundleCommand
{
    protected static $defaultDescription = 'Compiles the design system element declarations into each bundle\'s registry file';

    public function __construct(
        BundleService $bundleService,
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
                'Write nothing; fail when declarations and disk disagree, or when a registry is stale'
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
        $check = (bool) $input->getOption('check');

        if ($sources === []) {
            $io->warning(
                'No bundle declares itself a holder of design system elements. '
                . 'A bundle joins by implementing DesignSystemElementsBundleInterface.'
            );

            return self::SUCCESS;
        }

        $failed = [];

        foreach ($sources as $source) {
            $io->section($source->alias);

            $compilation = $this->registryService->compile($this->twig, $source);
            $inventory = $compilation->inventory;

            if ($input->getOption('table')) {
                $this->writeTable($io, $inventory->getEntries());
            }

            $io->writeln(
                sprintf(
                    '%d elements, %d of them in a single format, %d format decisions pending.',
                    $inventory->countEntries(),
                    $inventory->countByFormatsCount()[1],
                    count($compilation->pending)
                )
            );

            if ($output->isVerbose() && $compilation->pending !== []) {
                $io->listing($compilation->pending);
            }

            if (! $compilation->isClean()) {
                $io->listing($compilation->problems);
                $io->writeln(sprintf('<error>%d problems, nothing written.</error>', count($compilation->problems)));
                $failed[] = $source->alias;

                continue;
            }

            if ($check) {
                if ($this->registryService->isUpToDate($source, $inventory)) {
                    $io->writeln('Registry matches.');
                } else {
                    $io->writeln('<error>Registry is stale.</error>');
                    $failed[] = $source->alias;
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

        if ($failed === []) {
            $io->success($check ? 'Every registry matches its declarations and its assets.' : 'Done.');

            return self::SUCCESS;
        }

        $io->error(
            'Failed: ' . implode(', ', $failed) . '. Fix the declarations, then run '
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
            return $this->registryService->getSources();
        }

        foreach ($this->registryService->getSources() as $source) {
            if ($source->alias === $alias) {
                return [$source];
            }
        }

        return [];
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
                        static fn (ElementFormat $format): string => match (true) {
                            $entry->has($format) => 'x',
                            $entry->getAbsentJustification($format) !== null => '-',
                            default => '',
                        },
                        $formats
                    )
                ),
                $entries
            )
        );
    }
}
