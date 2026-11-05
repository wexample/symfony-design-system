<?php

namespace Wexample\SymfonyDesignSystem\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wexample\SymfonyDesignSystem\Service\FormSchemaService;

/**
 * The form schemas are a norm kept elsewhere (the `json-schema` service,
 * its `web/` directory) and copied here whole, as the norm says: a copy is
 * never edited where it lands. This command makes the copy, and beside each
 * field component the one-line schema pointing into it, so the registry
 * sees the component carries its contract. `--check` writes nothing and
 * fails when the copy differs from the source, or a component's schema is
 * not the pointer it should be.
 */
#[AsCommand(
    name: 'design-system:schemas:sync',
    description: 'Copies the form schemas from the json-schema norm beside the field components'
)]
class SyncFormSchemasCommand extends Command
{
    public function __construct(
        private readonly FormSchemaService $schemas,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('source', InputArgument::OPTIONAL, 'The norm\'s `web/` directory; left out, only the components\' pointers are checked or written')
            ->addOption('check', null, InputOption::VALUE_NONE, 'Write nothing; fail when the copy or a pointer is not what it should be');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = $input->getArgument('source');
        $check = (bool) $input->getOption('check');
        $problems = [];

        if ($source !== null) {
            $problems = [...$problems, ...$this->schemas->syncCopy($source, $check)];
        }

        $problems = [...$problems, ...$this->schemas->syncPointers($check), ...$this->schemas->checkPointerTargets()];

        if ($problems === []) {
            $io->success($check ? 'The schemas are as the norm says.' : 'The schemas are copied, the components point into them.');

            return self::SUCCESS;
        }

        $io->listing($problems);
        $io->error(sprintf('%d problems.', count($problems)));

        return self::FAILURE;
    }
}
