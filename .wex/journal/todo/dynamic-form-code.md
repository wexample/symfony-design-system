# Un champ de code dans `dynamic-form` (`code`)

Opened: 2026-11-05
Updated: 2026-11-05
Author: agent:main

## Pourquoi

`CodeInputType` (symfony-coding) colore son contenu sous l'éditeur posé sur le textarea, mais seulement dans un formulaire rendu par Twig. Dans `dynamic-form`, `FormDefinitionBuilder::buildKind()` retient le préfixe de bloc le plus proche pour lequel la norme a un schéma. La norme n'a pas de schéma `code` : le champ arrive donc en `textarea`, sans coloration ni langage.

Premier cas rencontré : le panel Templates du board de scénario Syrtis (`TemplateForm`, contenu Jinja, `language: 'jinja'`), édité dans le dock par `dynamic-form`.

## À faire

- Un kind `code` dans la norme json-schema des formulaires, avec `language` (et `language_map` / `language_source` pour un langage choisi par un autre champ, comme `CodeInputType`).
- `FormDefinitionBuilder` qui transmet ces options, comme pour `textarea` et ses `rows`.
- Le champ Vue de `dynamic-form` pour ce kind : l'éditeur de symfony-coding (`CodeEditorHelper`) posé sur le textarea, comme le fait son thème Twig.
- Pas de changement côté appelant : un `CodeInputType` passé à `form_definition_for()` arrive en `code` dès que le kind existe.
