# Les extensions de champ : un modèle pour ce qu'on attache à n'importe quel champ

Opened: 2026-11-06
Updated: 2026-11-06
Status: livré
Author: agent:main (design system)

## Pourquoi

Les versions (`versionsUrl` sur textarea et code) puis l'expression demandée par Syrtis poussaient vers une option par champ selon le besoin — l'inverse d'un design system. Un champ a deux natures : son **kind** (ce qu'il tient, fermé, la liste de la norme) et **ce qu'on lui attache** (orthogonal au kind, cumulable). Un super-champ par combinaison explose ; une option par champ dénature. Reste la troisième voie : le champ ne sait rien de ce qu'on lui attache, la base commune le dessine.

## Le modèle

- **Norme 3.0.0** : une propriété `extensions` dans `field_base`, une clé par extension, un schéma par extension (`extension_<nom>.schema.json`). Aucun schéma de kind ne bouge plus.
- **symfony-forms** : une option `extensions` sur tous les types (`FieldExtensionsTypeExtension` sur `FormType`), un contrat `FieldExtensionInterface` (nom, options, `buildForm`, `prepareSubmit`, `buildView`, `buildDefinition`, `buildDrawn`), un registre par services taggés (`wexample.symfony_forms.field_extension`). Le thème les dessine dans `form_row`, sous le contrôle ; `FormDefinitionBuilder` recopie `extensions`.
- **DS** : `form/extension/<nom>` (vue, style, schéma pointeur, élément de l'inventaire) ; registre `FieldExtensionsHelper.FIELD_EXTENSIONS` (composant, place, ordre) ; la base `_abstract/form-field` dessine les extensions de tout champ ; `dynamic-form` transmet `extensions` et fournit `dynamicFormContext` (id, saves, composants). Les extensions ne touchent que la valeur du champ, jamais son DOM : c'est ce qui permet de les cumuler.
- **Ouvert** : une application déclare la sienne avec les mêmes quatre faces (service taggé + `fieldExtensionsRegister`), dessinée pareil.

## Livré

- `versions` (refonte de field-versions) et `expression`.
- Syrtis : `MessageForm`, `SlotInputForm`, `AuditedContentFormTrait` sur `extensions.versions` ; `SlotConfigsForm` sur `extensions.expression` pour les configs `allow_expression`, sans plus d'aiguillage par la valeur.
- Portfolio : `form/from-server` (versions sur le résumé, expression sur le nombre de pages), `form/rendered` (versions).

## Hors périmètre

- Les kinds à valeur composite (pairs, tags) n'ont pas d'expression : à décider le jour où le besoin existe.
