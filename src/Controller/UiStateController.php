<?php

namespace Wexample\SymfonyDesignSystem\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Generic UI state persistence via PHP session.
 *
 * This is the default fallback implementation for apps using the DS bundle.
 * Apps that need server-side user config (e.g. Syrtis) override App::persistUiState
 * in their own AppManager class instead of calling this endpoint.
 *
 * JS hook: App::onMenuStateChange → App::persistUiState (override point)
 * Component: menu-collapsible-panel fires onMenuStateChange on every toggle.
 *
 * Session keys follow the format: ui.layout.menu.{menuId}
 * They are stored under the 'ui_state' session namespace.
 */
#[Route(path: '/_ui-state/', name: 'wexample_design_system_ui_state_')]
class UiStateController extends AbstractController
{
    #[Route(path: 'set', name: 'set', methods: [Request::METHOD_POST])]
    public function set(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $key = $data['key'] ?? null;

        if ($key === null) {
            return new JsonResponse(['success' => false, 'error' => 'Missing key'], 400);
        }

        $value = $data['value'] ?? null;
        $request->getSession()->set('ui_state.' . $key, $value);

        return new JsonResponse(['success' => true]);
    }
}
