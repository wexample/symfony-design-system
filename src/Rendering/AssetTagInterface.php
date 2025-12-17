<?php

namespace Wexample\SymfonyDesignSystem\Rendering;

interface AssetTagInterface
{
    public function fromAsset(Asset $asset): static;

    public function canAggregate(): bool;

    public function setCanAggregate(bool $canAggregate): static;

    public function getMedia(): ?string;

    public function setMedia(?string $media): static;

    public function getPath(): ?string;

    public function setPath(?string $path): static;

    public function getAsset(): ?Asset;

    public function setAsset(?Asset $asset): static;

    public function getUsageName(): string;

    public function setUsageName(string $usageName): static;

    public function getContext(): string;

    public function setContext(string $context): static;
}
