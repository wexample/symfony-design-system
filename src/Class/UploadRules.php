<?php

namespace Wexample\SymfonyDesignSystem\Class;

/**
 * What an upload address takes, signed into it with its directory: the kinds
 * of file (`accept`, read as the HTML attribute is — `.xlsx`, `text/csv`,
 * `image/*`, any one of them enough) and the largest size. Nothing said, any
 * file of any size, as before.
 */
final class UploadRules
{
    /**
     * @param string[] $accept
     */
    public function __construct(
        public readonly array $accept = [],
        public readonly ?int $maxSize = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            array_values(array_filter(array_map('strval', (array) ($data['a'] ?? [])))),
            isset($data['s']) ? (int) $data['s'] : null,
        );
    }

    // As signed into a token: short keys, nothing for what is not said.
    public function toArray(): array
    {
        return array_filter([
            'a' => $this->accept ?: null,
            's' => $this->maxSize,
        ], fn ($value) => null !== $value);
    }

    public function allowsSize(int $bytes): bool
    {
        return null === $this->maxSize || $bytes <= $this->maxSize;
    }

    /**
     * What the name alone says: true taken, false refused, null when only
     * the content can tell — a name matching no extension while a MIME type
     * is accepted.
     */
    public function allowsName(string $fileName): ?bool
    {
        if (! $this->accept) {
            return true;
        }

        $name = strtolower($fileName);

        foreach ($this->extensions() as $extension) {
            if (str_ends_with($name, $extension)) {
                return true;
            }
        }

        return $this->mimeTypes() ? null : false;
    }

    public function allowsMimeType(?string $mimeType): bool
    {
        $mimeType = strtolower((string) $mimeType);

        foreach ($this->mimeTypes() as $accepted) {
            if ($accepted === $mimeType
                || (str_ends_with($accepted, '/*') && str_starts_with($mimeType, substr($accepted, 0, -1)))) {
                return true;
            }
        }

        return false;
    }

    private function extensions(): array
    {
        return array_map('strtolower', array_filter($this->accept, fn ($entry) => str_starts_with($entry, '.')));
    }

    private function mimeTypes(): array
    {
        return array_map('strtolower', array_filter($this->accept, fn ($entry) => ! str_starts_with($entry, '.')));
    }
}
