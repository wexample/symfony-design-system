<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyDesignSystem\Service\UiStateService;

/**
 * Where the client writes what the interface remembers (App::persistUiState):
 * a menu folded, a zone dragged, a tool left open. Keys follow the format
 * `ui.layout.menu.{menuId}`, `ui.layout.zone.{id}.size`.
 *
 * Where they are kept is UiStateService's business, not this controller's:
 * the session, or the database once the app sets
 * `wexample_symfony_design_system.ui_state.storage` — the client is the same.
 */
#[Route(path: '/_ui-state/', name: 'wexample_design_system_ui_state_')]
class UiStateController extends AbstractController
{
    public function __construct(
        private readonly UiStateService $uiState,
    ) {
    }

    #[Route(path: 'set', name: 'set', methods: [Request::METHOD_POST])]
    public function set(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        // Several keys at once (`values`), or one (`key`, `value`).
        $values = is_array($data['values'] ?? null)
            ? $data['values']
            : (isset($data['key']) ? [$data['key'] => $data['value'] ?? null] : []);

        if ([] === $values) {
            return new JsonResponse(['success' => false, 'error' => 'Missing key'], 400);
        }

        foreach ($values as $key => $value) {
            $this->uiState->set((string) $key, $value);
        }

        return new JsonResponse(['success' => true]);
    }
}
