# Un lien « la suite est ailleurs » : `more-link`, sous une liste

Opened: 2026-11-06
Updated: 2026-11-06
Status: livré
Author: agent:main (Syrtis), pour l'agent du design system

## Pourquoi

Une liste courte montre les premiers éléments d'un ensemble — les 4 projets récents de la home
Syrtis, les dernières sessions, les derniers documents — et mène au reste sur une autre page :
« All projects ». Le DS n'a rien pour ça :

- `load-more` charge la suite **sur place** ;
- `text-link` est un lien **dans du texte** ;
- `toolbar--link` est un élément de **barre**, avec son padding et son fond au survol.

La home de Syrtis (`SYRTIS/local/api/front/pages/home/index.html.twig`) détourne donc une
`toolbar` + `toolbar--group align-right` + `toolbar--link` pour poser son lien. La v2 legacy
avait un lien plus discret, simple : c'est ce qui est voulu.

## Ce qui est demandé

Un composant `more-link` (twig et vue, comme `text-link`) :

- un libellé et un chevron après lui (`ph:bold/caret-right` ou `caret-double-right`) ;
- discret : la couleur du texte atténué, pas de fond, pas de cadre ; le survol le dit sans le
  transformer en bouton ;
- aligné à la fin de la ligne qu'il occupe, sans conteneur à ajouter autour ;
- posé seul sous une liste ou une grille, ou dans un `block--footer--end` (dont le commentaire
  parle déjà de « the link to the rest of what the block shows ») ;
- un `href`, un `label` ; rien d'autre de nécessaire.

Une entrée dans la démo, sous une grille de cards.

## Ensuite, côté Syrtis

La home remplace sa `toolbar` par `more-link`.

## Livré (2026-11-06)

`more-link` (twig et vue, `href` + `label`) : mots atténués, double chevron après, aucun fond ni cadre ; au survol les mots prennent l'encre de la page et le chevron avance d'un rien. Il prend la fin de sa ligne tout seul (`width: fit-content; margin-inline-start: auto; justify-self: end`) : sous une liste, dans une grille, dans un `block--footer--end`. Démo : page Blocks, sous la grille des cards-liens. La home Syrtis l'utilise à la place de sa toolbar.
