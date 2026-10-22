# Où s'ouvre un lien quand la page est embarquée : `target-embedded`

Opened: 2026-10-06
Updated: 2026-10-06
Author: agent:addon:ai/editor

## Constat

Une même page vit tantôt seule, tantôt dans un panel, une modal ou un embed. Le board ouvre
maintenant n'importe quel fichier d'une liste en panel, et un bundle (doc-explorer de HH)
peut rendre ce fichier avec sa propre page riche (`FileViewInterface`, symfony-wex). Deux
symptômes de la même question — dans quoi s'ouvre le lien que je clique ? :

- **Embed nommé absent** : la page de lecture d'un document ouvre son PDF dans l'embed
  `pdf-viewer` que pose l'explorateur. Ouverte en panel, cet embed n'existe pas :
  `loadIntoTarget()` → `EmbedService.load()` lève `ERR_EMBED_NOT_FOUND`
  (« No embed named "pdf-viewer" on this page »), rejet non géré, le bouton ne fait rien.
- **Lien interne « pleine page »** : depuis une page affichée dans un panel, un lien
  ordinaire remplace toute la page et fait perdre l'état de la page d'en dessous, alors
  qu'on veut souvent l'ouvrir dans un nouveau panel par-dessus.

Le bouton ne doit pas être masqué pour autant.

## Décision (opérateur, 2026-10-06)

Un seul concept, le **contexte embarqué** — pas un « fallback », qui suggère un échec :

- Un lien garde son `target` habituel (page, `panel`, `modal`, nom d'embed).
- Quand la page est **embarquée** (rendue dans un panel, une modal ou un embed) **ou** que la
  cible nommée n'existe pas sur la page, c'est **`target-embedded`** qui s'applique.
- Défaut de `target-embedded` : **`panel`**. Un panel dans un panel est voulu : il est
  lisible, et moins perturbant qu'alterner panel et modal.
- Réglable lien par lien, en HTML comme en JS :
  - `data-target-embedded="modal"` (ou `panel`, ou un nom d'embed) ;
  - `loadIntoTarget(app, target, href, { targetEmbedded: 'modal' })` ;
  - `none` : garder le comportement normal (un lien qui doit vraiment changer de page).
- Non concernés : liens externes, `target="_blank"`.

## À faire (design system / loader)

1. **Savoir qu'une page est embarquée** : la page rendue dans un panel / modal / embed le
   sait déjà (layout base `panel`, `modal`, `destPage` d'embed) ; l'exposer au moment de
   résoudre un clic.
2. **Résoudre la cible** dans `TargetHelper.loadIntoTarget()` : embed nommé absent →
   `targetEmbedded` (défaut `panel`) au lieu de lever. `EmbedService` gagne un `has(name)`.
3. **Liens ordinaires** : le gestionnaire de clics des liens internes (`button-target`,
   `button_target()`, liens de page sans target) applique `target-embedded` quand la page
   est embarquée.
4. **Empilement de panels** : vérifier que `PanelService` sait ouvrir un panel au-dessus
   d'un panel (fermeture, hash `panel.*` dans l'URL, retour).
5. Documenter l'attribut et l'option à côté de `target`.

## Lien

Même famille que la todo du board `4be02c6f69ed` (« Cible adaptative : un lien ouvert dans
un embed reste dans un embed »), qui en est le sens inverse.

## Critère de fin

Dans HH : un document ouvert en panel depuis l'onglet Findings du groupe QAL-T-01, bouton
PDF → le PDF s'ouvre dans un panel par-dessus, sans erreur ; un lien interne de cette page
ouvre aussi un panel au lieu de quitter la page ; ouverte seule (pleine page), la page de
lecture du document garde son embed `pdf-viewer`.
