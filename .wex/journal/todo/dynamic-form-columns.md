# Des champs côte à côte dans `dynamic-form` (`form--columns`)

Opened: 2026-11-05
Updated: 2026-11-05
Author: agent:main

## Pourquoi

Le DS sait poser les champs d'un formulaire en colonnes (`css/shapes/form/_layout.scss` : `form--columns`, `form--columns-2`, `form--span` pour un champ qui veut toute la ligne). Un formulaire rendu par Twig s'en sert par son `attr` et le `row_attr` de ses champs. `dynamic-form` n'en sait rien : il empile ses champs l'un sous l'autre, quelle que soit la place.

Premier cas : l'onglet Messages de l'éditeur de slot Syrtis (`SlotOutputMessageForm`) et le panel Templates — nom et content type, stamps et métadonnées pourraient tenir côte à côte, le contenu sur toute la ligne.

## À faire

- `FormDefinitionBuilder` transmet la mise en page : les classes `form--columns*` de l'`attr` du formulaire, `form--span` du `row_attr` d'un champ (ou un booléen `span` dans la définition, selon ce que la norme json-schema préfère).
- `dynamic-form` pose ces classes sur son conteneur de champs et sur chaque ligne, comme le thème Twig.
- Les actions restent sur toute la ligne (déjà le cas dans `_layout.scss`).
- Pas de changement côté appelant au-delà de l'`attr` et du `row_attr` qu'un formulaire Twig met déjà.

## Fait (agent du design system, 2026-11-05)

- La norme dit la mise en page, pas les classes : `columns` (1 à 3) sur le formulaire (`form.schema.json`), `span` sur un champ (`field_base.schema.json`). json-schema 2.3.0.
- `FormDefinitionBuilder` les lit là où un formulaire Twig les met déjà : `form--columns` (3) / `form--columns-2` (2) dans l'`attr` du formulaire, `form--span` dans le `row_attr` d'un champ. Testé.
- `dynamic-form` pose `form--columns` / `form--columns-2` sur son conteneur (grille) et `form--span` sur chaque champ qui le demande ; les actions restent sur toute la ligne (`_layout.scss`).
- Vérifié dans le navigateur sur le slot editor de Syrtis (définition passée à deux colonnes, contenu et métadonnées sur toute la ligne).

Côté Syrtis, il reste à mettre l'`attr` (`form--columns form--columns-2`) sur `SlotOutputMessageForm` / `TemplateForm` et le `row_attr` (`form--span`) sur leurs champs longs : le DS ne l'a pas fait à leur place.
