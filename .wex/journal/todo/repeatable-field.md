# Un champ répété : une liste de valeurs, une par élément (`repeatable`)

Opened: 2026-11-09
Updated: 2026-11-09
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
