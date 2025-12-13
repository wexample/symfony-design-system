<?php

namespace Wexample\SymfonyDesignSystem\Tests\Fixtures\App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle(),
            new \Wexample\SymfonyHelpers\WexampleSymfonyHelpersBundle(),
            new \Wexample\SymfonyTranslations\WexampleSymfonyTranslationsBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config/config.yaml');
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }
}
