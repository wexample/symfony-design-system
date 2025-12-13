<?php

namespace Wexample\SymfonyDesignSystem\Tests\Fixtures\App;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Wexample\SymfonyDesignSystem\WexampleSymfonyDesignSystemBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.yaml');
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }
}
