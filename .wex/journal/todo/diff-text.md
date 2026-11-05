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
