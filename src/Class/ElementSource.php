<?php

namespace Wexample\SymfonyDesignSystem\Class;

use ReflectionClass;
use Wexample\SymfonyDesignSystem\Interface\DesignSystemElementsBundleInterface;

/**
 * One bundle that signed up as holding elements, and where to look in it.
 *
 * The alias is the one twig already resolves templates by, so a path written in
 * the registry — `@WexampleSymfonyDesignSystemBundle/partials/spinner.html.twig`
 * — is a path anyone here can open without being told what it is relative to.
 */
class ElementSource
{
    public function __construct(
        public readonly string $bundleClass,
        public readonly string $alias,
        public readonly string $path,
        /**
         * Where the bundle keeps its twig extensions. Derived rather than
         * declared: a bundle putting them elsewhere has a bigger problem than
         * this registry.
         */
        public readonly string $twigNamespace,
    ) {
    }

    /**
     * @param class-string<DesignSystemElementsBundleInterface> $bundleClass
     */
    public static function fromBundle(string $bundleClass): self
    {
        return new self(
            $bundleClass,
            $bundleClass::getAlias(),
            rtrim((string) realpath($bundleClass::getDesignSystemElementsPath()), '/') . '/',
            (new ReflectionClass($bundleClass))->getNamespaceName() . '\\Twig\\',
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['bundle_class'],
            $data['alias'],
            $data['path'],
            $data['twig_namespace'],
        );
    }

    public function toArray(): array
    {
        return [
            'bundle_class' => $this->bundleClass,
            'alias' => $this->alias,
            'path' => $this->path,
            'twig_namespace' => $this->twigNamespace,
        ];
    }

    /**
     * How a path of this source is written down, everywhere.
     */
    public function qualify(string $relativePath): string
    {
        return '@' . $this->alias . '/' . $relativePath;
    }
}
