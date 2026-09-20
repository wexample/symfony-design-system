<?php

namespace Wexample\SymfonyDesignSystem\Service;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Wexample\SymfonyDesignSystem\Class\ElementDeclaration;
use Wexample\SymfonyDesignSystem\Class\ElementSource;

/**
 * The yaml files a bundle keeps about its elements, one per element.
 *
 * `elements/button.yml`, `elements/form/text-input.yml`: the file sits where the
 * key says, under the assets root beside the formats it speaks for. Reading is
 * the normal case; writing happens once per element, at seeding, and this
 * service refuses to overwrite so that no command can undo what a hand wrote.
 */
class ElementDeclarationService
{
    /**
     * Named apart because the scan has to know this directory holds
     * declarations about elements and not elements.
     */
    final public const string DIRECTORY = 'elements';

    final public const string EXTENSION = '.yml';

    public function getDirectory(ElementSource $source): string
    {
        return $source->path . self::DIRECTORY . '/';
    }

    public function getPath(
        ElementSource $source,
        string $key
    ): string {
        return $this->getDirectory($source) . $key . self::EXTENSION;
    }

    public function exists(
        ElementSource $source,
        string $key
    ): bool {
        return is_file($this->getPath($source, $key));
    }

    /**
     * @return array<string, ElementDeclaration> key => declaration
     */
    public function loadAll(ElementSource $source): array
    {
        $directory = $this->getDirectory($source);

        if (! is_dir($directory)) {
            return [];
        }

        $declarations = [];

        foreach ((new Finder())->files()->in($directory)->name('*' . self::EXTENSION) as $file) {
            $data = (array) Yaml::parseFile($file->getPathname());

            // The file's place is its key; a `key` written inside that says
            // otherwise is a file that was moved without being reread.
            $data['key'] = substr($file->getRelativePathname(), 0, -strlen(self::EXTENSION));

            $declarations[$data['key']] = ElementDeclaration::fromArray($data);
        }

        ksort($declarations);

        return $declarations;
    }

    /**
     * @return bool false when a file was already there, which is left as it is
     */
    public function write(
        ElementSource $source,
        ElementDeclaration $declaration
    ): bool {
        if ($this->exists($source, $declaration->key)) {
            return false;
        }

        $path = $this->getPath($source, $declaration->key);

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0o775, true);
        }

        $data = $declaration->toArray();
        // The key is the file's place, not its contents.
        unset($data['key']);

        file_put_contents(
            $path,
            $this->renderHeader($declaration) . Yaml::dump($data, 4, 2)
        );

        return true;
    }

    /**
     * What a reader opening the file needs to know before editing it — and what
     * a seeded file needs to say, since it was written by nobody.
     */
    private function renderHeader(ElementDeclaration $declaration): string
    {
        return "# Declares `{$declaration->key}` to the design system registry.\n"
            . "# Seeded from a scan of the assets; kept by hand from here on.\n"
            . "# nature: what the element is — to be chosen from the registry's vocabulary.\n"
            . "# formats: true when the element is expected in that format, or a\n"
            . "#   sentence saying why it does without. A format missing here is a\n"
            . "#   decision not yet made, and the check will name it.\n";
    }
}
