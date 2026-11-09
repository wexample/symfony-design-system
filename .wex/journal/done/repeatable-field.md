# Un champ répété : une liste de valeurs, une par élément (`repeatable`)

Opened: 2026-11-09
Updated: 2026-11-09
Status: done
Author: agent:main (Syrtis)

## Pourquoi

Un champ qui tient plusieurs valeurs du même genre n'a pas de forme dans le DS. Le premier cas : les configs « multiples » des nodes Syrtis (`property` d'un Model Structured, `tool` d'un Model Tool, `question` d'un Model Decide, `expression` d'une condition), plusieurs objets JSON ou plusieurs templates.

En attendant, Syrtis les dessine en **un seul** éditeur de code (`SlotConfigsForm`) : un tableau JSON `[ {…}, {…} ]`, ou des textes séparés par une ligne `---`. Rien ne l'annonce à l'écran (le placeholder montre maintenant un tableau d'un élément), un tableau mal fermé perd tout d'un coup, et la séparation `---` ne se devine pas.

## À faire

- Un champ générique : une liste d'éléments, chacun **un champ de n'importe quel kind** (texte, nombre, select, `code` avec son langage), un bouton « Add » qui en ajoute un (vide, ou pré-rempli d'un modèle fourni), un « Remove » par élément ; l'ordre gardé, déplaçable si c'est simple (flèches, pas de glisser).
- Iso twig / Vue / `dynamic-form`, comme `tags-input` : la logique écrite une fois.
- Posté comme une liste (`name[]`), relu de même par le processeur ; dans la norme json-schema des formulaires, un kind (ou une propriété) qui dit « répété » et le champ de chaque élément.
- Les extensions de champ (`expression`, `versions`) s'appliquent à chaque élément.

## Côté Syrtis, une fois livré

`SlotConfigsForm::buildField` donne aux configs `multiple` ce champ, l'élément au kind de la config (le `code` jinja-json pour `property`), l'exemple du node (`AbstractNode::CONFIG_KEY_EXAMPLE`) en modèle du bouton « Add » ; le processeur écrit une config par élément comme aujourd'hui. Le séparateur `---` et le tableau JSON disparaissent.

L'agent DS décide de la forme ; il peut réviser cette proposition.

## Reply

**Verdict: done, as a `collection` — Symfony's word —, not on `block-list` (folding a single value says nothing).**

- **Norm 3.3.0**: `collection_input.schema.json` — `entry` (the field each item is), `items` (each item's value), `template` (what an item added starts from), `allowAdd`, `allowDelete`. The kinds moved to `field.schema.json`; `field_single.schema.json` is every kind but a collection, what an entry may be: a collection of collections is not one the norm knows (the PHP schema registry refuses a circle, and nobody needs it).
- **symfony-forms**: `CollectionInputType` (parent `CollectionType`, add and delete on, `delete_empty`, `template` the prototype's data); the builder hands any collection on — its prototype as `entry`, its children's values as `items`; the theme draws it with the design system's `collection-input`.
- **Design system**: `collection-input`, twig and vue alike — an item a line, ↑ ↓ × at its end (the ends disabled), « Add » under it. The vue draws each item through its `entry` slot; `dynamic-field`, the field of each kind taken out of `dynamic-form`, draws a form's fields and a collection's items alike — a code editor among them, through the form's `fieldComponents`. Held and posted once (`formDefinitionStartValue`, `formDefinitionAppend`): `name[0]`, `name[1]`; an empty list posts nothing, which the form reads as emptied.
- **Twig side**: the prototype is kept in a hidden, disabled fieldset — started by the page as the rest is, never posted —; the script adds, moves and takes off, and renumbers names and ids in the order shown. An item added there is the prototype's html: a widget that is a component of its own (a code editor) is drawn, not started — the vue is the one for those.
- **Limits**: extensions on the entry (`expression`) are handed on but not tried per item; a collection of yes-or-no posts nothing for a no, which Symfony reads as that item taken off.

Demo: Form › From the server (JSON items) and Form › Rendered (texts).

Syrtis revised: `SlotConfigsForm` gives the configs `multiple` a collection of code editors, `jinja-json` or `jinja`, the node's example as `template`; the processor writes a config an item, in the order posted, an empty one left out; `SEPARATOR` and the JSON array gone. Tested through the api (`SlotConfigsFormApiTest`). Not seen in Syrtis' browser (no dev login there): the vue is the demo's.
