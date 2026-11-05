# Le diff d'un texte, et sa version Vue (`diff`)

Opened: 2026-11-05
Updated: 2026-11-05
Author: agent:main

## Pourquoi

Le composant `diff` du DS compare des valeurs champ par champ (`rows` de `field`, `before`, `after`), en Twig seulement. Ce qu'il faut pour un contenu versionné est autre chose : le diff ligne à ligne d'un long texte (un template Jinja, un prompt), lu dans le navigateur.

Premier cas : l'historique du contenu d'un message émis par un slot Syrtis, d'un template de scénario — lu depuis l'audit de symfony-activity (`#[Auditable]`, l'ancienne et la nouvelle valeur du champ `content`) et affiché par `VersionedContentType`.

## À faire

- Un mode texte du `diff` : deux textes en entrée, les lignes ajoutées, retirées et gardées, éventuellement les mots changés dans une ligne ; unifié (une colonne) ou côte à côte.
- Sa version Vue (`diff.vue`), jumelle du Twig, pour un formulaire qui compare une version au brouillon sans recharger.
- L'algorithme côté JS sans dépendance lourde (Myers ligne à ligne suffit) ; côté PHP, le même rendu pour une page.

## Fait (agent du design system, 2026-11-05)

- **Un élément à part, `diff-text`**, pas un mode du `diff` : celui-ci compare des valeurs champ par champ, l'autre des lignes de texte — ni la même donnée ni le même dessin.
- Calcul `DiffTextHelper` écrit deux fois, PHP (`src/Helper`) et TS (`js/Helper`), mêmes cas testés des deux côtés : Myers ligne à ligne ; dans une suite de changements, la i-ème ligne retirée face à la i-ème ajoutée reçoit ses mots, ceux qui changent marqués (Myers sur les mots) ; les lignes gardées à plus de `context` lignes d'un changement repliées ; côte à côte, une ligne retirée face à celle qui l'a remplacée.
- Twig : `diff_text(before, after, { mode: 'unified'|'split', context, words, before_label, after_label })`. Vue : `components/diff-text` (`before`, `after`, mêmes options), un pli s'ouvre d'un clic.
- En-tête : les deux libellés, `+N −M`. Rien de changé : « Nothing changed. ».
- Démo : portfolio `data/properties` (le serveur en unifié, la vue côte à côte). Vérifié dans le navigateur : les mêmes lignes des deux côtés, les mots changés marqués, un pli ouvert.

Ce que ça débloque : les versions du champ de code de `dynamic-form` (`dynamic-form-code.md`) ont maintenant de quoi afficher une version contre le brouillon.
