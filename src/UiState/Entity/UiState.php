<?php

namespace Wexample\SymfonyDesignSystem\UiState\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyDesignSystem\UiState\Repository\UiStateRepository;

/**
 * One thing the interface remembers, kept in the database when the app asks
 * for it (UiStateStorage::User or ::Shared). Outside `src/Entity` on purpose:
 * that directory is mapped in every app the bundle runs in, and this table is
 * only created in those that chose it.
 */
#[ORM\Entity(repositoryClass: UiStateRepository::class)]
#[ORM\Table(name: 'ui_state')]
#[ORM\UniqueConstraint(name: 'ui_state_owner_key', columns: ['owner', 'state_key'])]
class UiState extends AbstractEntity
{
    /**
     * The identifier of the user it belongs to, empty when shared by everybody.
     */
    #[ORM\Column(type: Types::STRING, length: 180)]
    private string $owner;

    #[ORM\Column(name: 'state_key', type: Types::STRING, length: 255)]
    private string $key;

    #[ORM\Column(name: 'state_value', type: Types::JSON)]
    private mixed $value = null;

    public function __construct(
        string $owner,
        string $key,
    ) {
        parent::__construct();

        $this->owner = $owner;
        $this->key = $key;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }
}
