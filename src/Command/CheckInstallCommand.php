<?php

namespace Wexample\SymfonyDesignSystem\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckInstallCommand extends Command
{
    protected static $defaultName = 'design-system:check-install';

    protected function configure(): void
    {
        $this
            ->setDescription('Check dependencies installation');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        // Spécifiez le chemin vers votre fichier package.json ici.
        // Dans cet exemple, on suppose qu'il est à la racine du projet.
        $packageJsonPath = getcwd().'/package.json';

        if (!file_exists($packageJsonPath)) {
            $io->error('No package.json file found.');

            return Command::FAILURE;
        }

        $packageJsonContent = file_get_contents($packageJsonPath);
        $packageJsonData = json_decode($packageJsonContent, true);

        // Liste des dépendances Node.js nécessaires
        $neededDependencies = ['sass-loader'];

        foreach ($neededDependencies as $dependency) {
            if (!isset($packageJsonData['dependencies'][$dependency]) && !isset($packageJsonData['devDependencies'][$dependency])) {
                $io->error("Mission node module '{$dependency}'. Run `npm install {$dependency}, or yarn add {$dependency}`.");

                return Command::FAILURE;
            }
        }

        $io->success('All dependencies are installed.');

        return Command::SUCCESS;
    }
}
