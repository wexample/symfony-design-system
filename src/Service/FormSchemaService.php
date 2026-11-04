<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * The form schemas the design system holds: a copy of the `web/` directory
 * of the json-schema norm (`assets/schemas/web/`), whole, so its relative
 * `$ref`s resolve; and, beside each field component, `<x>.schema.json`
 * pointing into the copy — the contract the component is checkable against,
 * where the registry expects it (ElementFormat::SCHEMA).
 *
 * A field's schema is named as its kind is sent: `text_input.json` for the
 * component `form/text-input`.
 */
class FormSchemaService
{
    final public const string DIRECTORY = 'schemas/web';

    private const string COMPONENTS = 'components/form';

    private const string SUFFIX = '_input.json';

    public function __construct(
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
    }

    public function getDirectory(): string
    {
        return $this->getAssetsPath() . self::DIRECTORY;
    }

    /**
     * The schema of a kind of field, or of the form, by its file name
     * (`text_input`, `form`), as the copy holds it: `$ref`s left as they are.
     */
    public function getPath(string $name): string
    {
        return $this->getDirectory() . '/' . $name . '.json';
    }

    /**
     * The kinds of field the copy knows: `text`, `textarea`…
     *
     * @return string[]
     */
    public function getFieldKinds(): array
    {
        $kinds = [];

        foreach ($this->findFieldSchemas($this->getDirectory()) as $file) {
            $kinds[] = substr($file->getFilename(), 0, -strlen(self::SUFFIX));
        }

        sort($kinds);

        return $kinds;
    }

    /**
     * The copy made from the norm's directory, file for file; checking, the
     * differences named instead.
     *
     * @return string[] what differs, when checking
     */
    public function syncCopy(string $source, bool $check): array
    {
        $problems = [];
        $target = $this->getDirectory();

        if (! is_dir($source)) {
            return [sprintf('%s is not a directory.', $source)];
        }

        $sources = [];

        foreach ((new Finder())->files()->in($source)->name('*.json')->depth(0) as $file) {
            $sources[$file->getFilename()] = $file->getContents();
        }

        $copies = [];

        if (is_dir($target)) {
            foreach ((new Finder())->files()->in($target)->name('*.json')->depth(0) as $file) {
                $copies[$file->getFilename()] = $file->getContents();
            }
        }

        foreach ($sources as $name => $contents) {
            if (($copies[$name] ?? null) === $contents) {
                continue;
            }

            if ($check) {
                $problems[] = sprintf('%s/%s %s the norm.', self::DIRECTORY, $name, isset($copies[$name]) ? 'differs from' : 'is missing from the copy of');

                continue;
            }

            $this->filesystem->dumpFile($target . '/' . $name, $contents);
        }

        foreach (array_diff_key($copies, $sources) as $name => $contents) {
            if ($check) {
                $problems[] = sprintf('%s/%s is no longer in the norm.', self::DIRECTORY, $name);

                continue;
            }

            $this->filesystem->remove($target . '/' . $name);
        }

        return $problems;
    }

    /**
     * Beside each field component the copy has a schema for, the pointer into
     * it; checking, the pointers that are not so named.
     *
     * @return string[]
     */
    public function syncPointers(bool $check): array
    {
        $problems = [];
        $assets = $this->getAssetsPath();

        foreach ($this->findFieldSchemas($this->getDirectory()) as $file) {
            $kind = substr($file->getFilename(), 0, -strlen(self::SUFFIX));
            $component = str_replace('_', '-', $kind) . '-input';
            $directory = $assets . self::COMPONENTS . '/' . $component;

            if (! is_dir($directory)) {
                continue;
            }

            $path = $directory . '/' . $component . '.schema.json';
            $expected = $this->buildPointer($component, $file->getFilename());

            if (is_file($path) && file_get_contents($path) === $expected) {
                continue;
            }

            if ($check) {
                $problems[] = sprintf('%s/%s/%s.schema.json %s point into %s/%s.', self::COMPONENTS, $component, $component, is_file($path) ? 'does not' : 'is missing: it should', self::DIRECTORY, $file->getFilename());

                continue;
            }

            $this->filesystem->dumpFile($path, $expected);
        }

        return $problems;
    }

    private function buildPointer(string $component, string $file): string
    {
        return json_encode([
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            '$id' => $component . '.schema.json',
            'description' => 'The contract of the component: the schema of the kind of field it draws, copied from the json-schema norm (design-system:schemas:sync).',
            '$ref' => '../../../' . self::DIRECTORY . '/' . $file,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
    }

    private function findFieldSchemas(string $directory): Finder
    {
        return (new Finder())->files()->in($directory)->name('*' . self::SUFFIX)->depth(0);
    }

    private function getAssetsPath(): string
    {
        return dirname(__DIR__, 2) . '/assets/';
    }
}
