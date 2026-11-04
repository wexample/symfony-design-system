# Workspace composable (façon Kibana) : tiling DS, widgets et channel dans symfony-workspace, dispositions persistées

Opened: 2026-11-02
Updated: 2026-11-02
Author: agent:main

## But
Écran composable : zones découpables, registre de widgets, événements, disposition sauvegardée, liée à une entité.

## Fait
- **DS** 63afa1b2, 6eccdb60, 8dcfeab3, 904fb503 (zone-tiling, ids aléatoires + réparation des doublons) ; 70c5a45c (chat : `parts` thread/composer, pairs = même conversation et mêmes parties).
- **symfony-workspace** a8b678a, a5e7266, a6b29ed, cba6358 (`context` passé aux widgets, vue résolue côté serveur via `ComponentPathHelper::resolveView`).
- **Démo portfolio** 73f76be + 217765b ; revérifiée en headless après cba6358.
- **Syrtis** 32bd6773, 17319702, 7b8f9e09, b59e8fa1 (board sur l'accueil du scénario, plein écran, sans titre ni panneau) ; f764ba4b : 3 widgets `scenario-job` (stage vide), `session-messages` (fil seul), `session-sender` (saisie + options), session partagée par l'événement `session` du board et gardée dans la config de chaque zone ; `session-picker` émet `session` (embed optionnel, `pageUrl`) ; page chat de session supprimée (contrôleur, template, menu) ; workbench réduit à son job ; `App\Workspace` chargé comme services. Test `ScenarioHomePageTest` OK.

## Reste
- Syrtis : `composer.json` / `composer.lock` (ajout symfony-workspace) non commités, mêlés au travail d'un autre agent.
- Watcher Syrtis à relancer, puis vérifier les widgets à l'écran (pas de session headless possible sans identifiants).
- `ProjectScenarioPageTest` cassé avant mes changements (va sur l'accueil et y cherche le formulaire d'édition, passé en Settings dans 778b9826).
- Liste de messages : filtres configurables (ex. tampon `chat`) à venir.
- `composer.json` de symfony-workspace : dépendances doctrine / security à déclarer.
