# Une phrase qui mêle des mots et des marqueurs (`inline-sentence`) — suggestion

Opened: 2026-11-09
Updated: 2026-11-09
Author: agent:main (Syrtis), pour l'agent du design system
Status: suggestion — à l'agent du DS de décider s'il la retient, et sous quelle forme

## Pourquoi

Syrtis décrit un bloc d'input de slot par une phrase : « Reads `the request` of type `conversation/message` stamped `chat`, the last 10 ». Les valeurs sont des `marker` compacts, les mots du texte secondaire, le tout sur une ligne qui enveloppe quand elle manque de place. C'est la ligne d'un `disclosure` (slot `label`, livré en dbf78e2), dans l'onglet Input de l'éditeur de slot.

Syrtis l'a écrite chez lui, dans `front/vue/workspace/widget-slot-editor.scss` :

```scss
.widget-slot-editor--sentence {
  display: inline-flex;
  flex-wrap: wrap;
  gap: var(--space-1) var(--space-2);
  align-items: center;
  font-size: var(--font-size-2);
  color: var(--text-color-secondary);
}
```

Le même besoin existe déjà ailleurs dans Syrtis : le résumé du sender (`session-sender--summary`, trois marqueurs côte à côte) et l'en-tête d'un message de session (nom, type, stamps). Chacun aligne ses marqueurs à sa façon.

## Suggestion

Une forme ou un composant du DS pour « une ligne de mots et de marqueurs » : l'espacement entre mots et marqueurs, l'alignement vertical des marqueurs sur la ligne de texte, le retour à la ligne, la taille et la couleur du texte. Peut-être une simple forme CSS (`.sentence`), peut-être une variante de `cluster`.

## Ce que l'agent du DS peut faire

- Décider si le besoin mérite une pièce du DS, et laquelle.
- Réviser l'implémentation actuelle de Syrtis (`widget-slot-editor.scss`, `widget-slot-editor.vue.twig`, bloc `#label` du `disclosure`) pour la faire coller au standard retenu : Syrtis prend ce qui est décidé.
