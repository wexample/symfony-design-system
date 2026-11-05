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

## Les versions d'un contenu

Syrtis édite aussi en `dynamic-form` des contenus versionnés (son `VersionedContentType`, enfant de `CodeInputType` : le code, et ses versions antérieures à choisir, à relire ou à reprendre). Leurs versions viennent désormais de l'audit (symfony-activity, `ActivityFieldHistoryService::findEarlierVersions()`).

Une définition de `dynamic-form` se construit sans entité (`form_definition_for(…, null, { id_placeholder })`) : les versions d'un enregistrement ne peuvent pas y être. Le champ `code` du DS gagnerait donc une source de versions par adresse — `versions_url` avec le même `__id__` que l'action —, qu'il lit quand l'enregistrement est ouvert, et qu'il affiche avec le diff de texte (`diff-text.md`).


## Fait (agent du design system, 2026-11-05)

- Norme : `web/code_input.schema.json` (kind `code`) : `language`, `languageField` (le **nom** du champ qui donne le langage, pas l'id Twig), `languageMap`, `rows`.
- `FormDefinitionBuilder` les transmet ; `language_source` (l'id Twig) est ramené au nom du champ. Testé avec un type fixture au préfixe `code_input` (symfony-forms ne dépend pas de symfony-coding).
- **Pas d'import de symfony-coding dans le DS** (il dépend du DS) : `dynamic-form` accepte `fieldComponents` — un kind → `{ component, props(field, draft) }` — et dessine ces champs en leur donnant ce que tout champ reçoit. symfony-coding fournit `dynamicFormCodeField` (`js/Helper/DynamicFormHelper.ts`) : l'éditeur `code-input`, son langage lu de la définition ou du champ qui le nomme (dans le brouillon, donc il suit le select de format). Sans l'entrée, un `code` est dessiné en textarea.
- Syrtis : branché dans les quatre `dynamic-form` qui portent du code (slot editor : config, messages, marking ; liste des templates). Vérifié dans le navigateur : le contenu d'un message émis s'écrit dans l'éditeur, coloré en Jinja.

**Pas fait : les versions.** `versions_url` et leur affichage demandent le diff de texte (`diff-text.md`) et un choix de version dans l'éditeur vue, qui n'existent pas ; l'agent de Syrtis travaille en ce moment sur les versions par l'audit (`AuditedContentFormTrait`). À reprendre ensemble : le contrat (`versionsUrl` avec `__id__`, réponse `[{ label, content }]`) est prêt à être écrit dans la norme dès que l'interface l'est.
