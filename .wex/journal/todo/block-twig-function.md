# Réévaluer une fonction Twig pour les blocs (`block--card`, `block--padded`…)

Opened: 2026-10-08
Updated: 2026-10-08
Author: agent:main

## Pourquoi

Demandé par le propriétaire le 2026-10-08, en relisant les écrans memberships de symfony-user-ds : tout y passe par les fonctions du DS (`data_table`, `properties`, `marker`, `button_*`, `form_load`), sauf les blocs, écrits à la main :

```twig
<section class="block block--card">
    <div class="block--title">
        <span class="block--title-leading">{{ icon('ph:bold/users-three') }}</span>
        <span class="block--title-label">{{ '@page::add.title' | trans }}</span>
    </div>
    <div class="block--body">…</div>
    <div class="block--footer">…</div>
</section>
```

Les styles sont dans `assets/css/shapes/_block.scss` (et des variantes par skin : `layout.skin.aqua.scss`, `layout.skin.win95.scss`). Aucune fonction ne produit ce balisage : chaque page le recopie.

## L'état des lieux (2026-10-08, templates `.twig` des packages)

- 225 `block block--padded`, 52 `block block--card`, quelques `--square`, `--placeholder`, `--body`, `--fill`.
- Fichiers : symfony-design-system-demo 51, symfony-user-ds 17, symfony-user-demo 6, symfony-tunnels-demo 5, symfony-wex 3, puis 1 ou 2 dans media-demo, charts-demo, ai, translations-demo, mail-demo, activity-ds, design-system.

## À trancher avant tout code

1. **Le besoin.** Un balisage recopié 280 fois dérive (un `block--title-leading` oublié, une icône hors de son span). Mais `--padded` n'est souvent qu'un `<section>` avec une classe : une fonction n'y gagne rien. Le gain se concentre sur `--card` (titre, icône, corps, pied).
2. **La forme.** Le contenu d'un bloc est du Twig arbitraire (formulaires, tableaux, autres composants). Une fonction `block_card(title, icon, body)` oblige à capturer le corps dans un `{% set %}` ; un `{% embed %}` d'un template du DS (`@WexampleSymfonyDesignSystemBundle/components/block/card.html.twig`, blocs `body` et `footer`) garde le contenu en place. Comparer avec ce que font déjà les autres composants à contenu libre du DS.
3. **Le lien avec les schémas.** `component-schemas.md` décrit chaque composant pour le page builder : un bloc carte en serait un, un balisage libre non.
4. **La migration.** Remplacer les 52 cartes existantes, ou seulement écrire les nouvelles avec ; et la page de démonstration du DS qui montre les blocs.

## Si on le fait

- Le composant dans le DS, sa page de démo, son schéma.
- Les cartes de symfony-user-ds et symfony-user-demo en premier (les plus récentes, 23 fichiers), puis les autres au fil des passages.
