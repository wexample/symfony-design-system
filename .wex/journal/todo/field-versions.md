# Les versions d'un champ texte : relire, comparer, reprendre (`versionsUrl`)

Opened: 2026-11-05
Updated: 2026-11-05
Status: livré
Author: agent:main (Syrtis), pour l'agent du design system

## Pourquoi

Un contenu édité dans un formulaire — par une personne, demain par un agent IA — doit pouvoir montrer ce qu'il était avant : le relire, le comparer au brouillon, le reprendre. Syrtis en a besoin sur quatre champs déjà (le message émis par un slot, un template de scénario, un message global, bientôt les prompts), et l'édition par des agents le demandera sur n'importe quel champ texte.

Toutes les pièces existent :
- l'historique : l'audit de symfony-activity (`#[Auditable]`), relu par `ActivityFieldHistoryService::findEarlierVersions()` ;
- le diff : `diff-text` (DS, Twig et Vue, livré) ;
- l'éditeur : `code-input` de symfony-coding, branché dans `dynamic-form` par `fieldComponents`.

Ce qui manque est l'interface qui les assemble autour d'un champ.

## Forme retenue

Pas un nouveau kind : une **option de tout champ texte** (`textarea`, `code`), que `dynamic-form` et le thème Twig dessinent pareil.

- La définition du champ porte `versionsUrl`, avec le même `__id__` que l'action du formulaire. Une définition se construit sans entité (`form_definition_for(…, null, { id_placeholder })`) : les versions d'un enregistrement ne peuvent pas y être, elles sont lues à l'ouverture.
- Réponse attendue : une collection `[{ label, content, date, author }]`, la plus récente d'abord, sans la version courante. `label` est déjà formulé par l'application (« 2026-11-05 15:57 · weeger », « — » pour une valeur d'avant l'audit).
- Autour du champ, une barre discrète : le choix d'une version (la courante en tête, « brouillon » tant qu'il diffère), le diff de la version choisie avec le brouillon (`diff-text`, unifié par défaut), une action « reprendre cette version » qui met son contenu dans le brouillon sans enregistrer. Rien quand la réponse est vide.
- Le brouillon n'est jamais perdu : choisir une version ne l'écrase qu'à « reprendre », et revenir sur « brouillon » le rend tel qu'il était.
- Côté Symfony : une option de champ (`versions_url`, avec `__id__`) que `FormDefinitionBuilder` transmet en `versionsUrl`, à écrire dans la norme json-schema des champs texte.
- Côté `code` : l'éditeur de symfony-coding garde la barre du DS au-dessus de lui ; pas de logique de versions dans symfony-coding.

## Côté Syrtis, prêt

- `GET /_api/slot-output-message/versions/{id}` et `GET /_api/template/versions/{id}` répondent déjà au format ci-dessus (`ContentVersionNormalizer`, Syrtis e65231a).
- Une fois l'option livrée : `VersionedContentType` se réduit à `versions_url`, et le composant Twig `@front/components/versioned-content` de Syrtis disparaît.

## Hors périmètre

- L'autocomplétion (mise de côté).
- L'auteur « agent pour le compte de » : symfony-activity.
- La validation d'un JSON qui est un template Jinja : elle porte sur le rendu, pas sur le texte.

## Livré (2026-11-05)

- Norme 2.4.0 : `versionsUrl` sur `textarea_input` et `code_input`.
- symfony-forms : option `versions_url` de `TextareaInputType` (héritée par `CodeInputType`), transmise en `versionsUrl` par `FormDefinitionBuilder` ; `versions_url_resolved` pour le champ dessiné (l'id de l'entité racine à la place de `__id__`) ; bloc `wex_field_versions` du thème, repris par `code_input_widget` de symfony-coding.
- DS : `form/field-versions` (vue + style), `FieldVersionsHelper`, `formDefinitionVersionsUrl()` ; `dynamic-form` dessine la barre au-dessus du champ dès que l'entité est connue et relit les versions après enregistrement ; côté Twig la barre suit le champ par son id et écoute le succès du formulaire. Démo : `form/from-server` (vue) et `form/rendered` (twig).
- Syrtis : `AuditedContentFormTrait` et `MessageForm` passent par `CodeInputType` + `versions_url` ; `GET /_api/message/versions/{id}` ajouté ; `VersionedContentType`, `@front/components/versioned-content` et le bloc de thème supprimés ; `MessageFormProcessor` ne renvoie plus les versions.

Si les endpoints par entité se multiplient : un générique dans symfony-activity (`/_api/activity/versions/{entity}/{id}/{field}`), l'option ne change pas.

## Refait (2026-11-06) : une extension de champ, pas une option de textarea

`versionsUrl` posé sur textarea/code était du cas par cas. Le modèle retenu (ticket `field-extensions.md`) : une clé `extensions` sur tout champ, chaque extension déclarée une fois (norme, option Symfony, composant `form/extension/<nom>`, inventaire), dessinée par la base des champs. Les versions sont `extensions.versions.url` ; Syrtis, le portfolio et la démo sont passés dessus. Norme 3.0.0, sans rétrocompatibilité.
