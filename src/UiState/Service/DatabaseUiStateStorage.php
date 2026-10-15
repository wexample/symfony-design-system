<?php

namespace Wexample\SymfonyDesignSystem\UiState\Service;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Wexample\SymfonyDesignSystem\DependencyInjection\WexampleSymfonyDesignSystemExtension;
use Wexample\SymfonyDesignSystem\Enum\UiStateStorage;
use Wexample\SymfonyDesignSystem\Interface\UiStateStorageInterface;
use Wexample\SymfonyDesignSystem\Service\UiState\SessionUiStateStorage;
use Wexample\SymfonyDesignSystem\UiState\Entity\UiState;
use Wexample\SymfonyDesignSystem\UiState\Repository\UiStateRepository;

/**
 * The database: what the interface remembers follows a user from one machine
 * to another (UiStateStorage::User), or is the one memory of a tool without
 * accounts (UiStateStorage::Shared). Per user, a visitor not signed in has no
 * row of their own and keeps theirs in the session.
 *
 * A page reads many keys and writes few: the rows of the owner are read once
 * per request and written one at a time.
 */
class DatabaseUiStateStorage implements UiStateStorageInterface
{
    private const SHARED_OWNER = '';

    private readonly bool $shared;

    /** @var array<string, array<string, UiState>> by owner, then by key */
    private array $loaded = [];

    public function __construct(
        private readonly UiStateRepository $repository,
        private readonly Security $security,
        private readonly SessionUiStateStorage $session,
        #[Autowire(param: WexampleSymfonyDesignSystemExtension::PARAMETER_UI_STATE_STORAGE)]
        string $storage,
    ) {
        $this->shared = UiStateStorage::Shared === UiStateStorage::from($storage);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $owner = $this->owner();

        if (null === $owner) {
            return $this->session->get($key, $default);
        }

        $state = $this->states($owner)[$key] ?? null;

        return $state ? $state->getValue() : $default;
    }

    public function all(): array
    {
        $owner = $this->owner();

        if (null === $owner) {
            return $this->session->all();
        }

        return array_map(
            static fn (UiState $state): mixed => $state->getValue(),
            $this->states($owner)
        );
    }

    public function set(string $key, mixed $value): void
    {
        $owner = $this->owner();

        if (null === $owner) {
            $this->session->set($key, $value);

            return;
        }

        $states = $this->states($owner);
        $state = $states[$key] ?? null;

        if (null === $value) {
            if ($state) {
                $this->repository->remove($state);
                unset($this->loaded[$owner][$key]);
            }

            return;
        }

        if (! $state) {
            $state = $this->repository->createNewUiState($owner, $key);
            $this->loaded[$owner][$key] = $state;
        }

        $state->setValue($value);
        $this->repository->save($state);
    }

    /**
     * Null when the memory has no row to be kept in: per user, nobody signed in.
     */
    private function owner(): ?string
    {
        if ($this->shared) {
            return self::SHARED_OWNER;
        }

        return $this->security->getUser()?->getUserIdentifier();
    }

    /**
     * @return array<string, UiState>
     */
    private function states(string $owner): array
    {
        return $this->loaded[$owner] ??= $this->repository->findByOwnerIndexed($owner);
    }
}
