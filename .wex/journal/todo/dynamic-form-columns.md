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
