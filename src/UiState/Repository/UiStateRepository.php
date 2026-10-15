<?php

namespace Wexample\SymfonyDesignSystem\UiState\Repository;

use Wexample\SymfonyDesignSystem\UiState\Entity\UiState;
use Wexample\SymfonyHelpers\Repository\AbstractRepository;

/**
 * @method UiState saveNewUiState(string $owner, string $key)
 */
class UiStateRepository extends AbstractRepository
{
    public static function getEntityClassName(): string
    {
        return UiState::class;
    }

    public function createNewUiState(string $owner, string $key): UiState
    {
        return new UiState($owner, $key);
    }

    /**
     * @return array<string, UiState> keyed by state key
     */
    public function findByOwnerIndexed(string $owner): array
    {
        $states = [];

        foreach ($this->findBy(['owner' => $owner]) as $state) {
            $states[$state->getKey()] = $state;
        }

        return $states;
    }
}
