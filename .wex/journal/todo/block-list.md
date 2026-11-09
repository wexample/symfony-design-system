# Une liste ordonnée de blocs, chacun une ligne repliable (`block-list`) — suggestion

Opened: 2026-11-09
Updated: 2026-11-09
Author: agent:main (Syrtis), pour l'agent du design system
Status: suggestion — à l'agent du DS de décider s'il la retient, et sous quelle forme

## Pourquoi

Syrtis édite à deux endroits une **liste ordonnée d'éléments**, chacun une ligne qui se replie sur son formulaire :

- l'onglet **Input** de l'éditeur de slot : les blocs d'une part (sélecteurs et textes), un `disclosure` par bloc, la phrase qui le décrit sur la ligne, son `dynamic-form` dessous ; au bout de la ligne, monter, descendre, supprimer ; sous la liste, « Add a selector » / « Add a text » (api 4b96bd7) ;
- l'onglet **Messages** du même éditeur : les messages émis par le slot, un `block--compact` par message, les mêmes flèches et la même corbeille, un bouton d'ajout dessous.

Les deux sont écrits à la main dans `front/vue/workspace/widget-slot-editor.vue.twig`, avec leurs boutons `action-icon`, leurs libellés et leurs appels à l'api (`move/{id}` avec `up`/`down`, `delete/{id}`, `create`).

## Suggestion

Un composant du DS pour ce motif, qui porterait :

- l'ordre : monter / descendre au bout de chaque ligne (le drag and drop volontairement mis de côté pour l'instant : les flèches suffisent, on verra à l'usage) ;
- la suppression, avec ou sans confirmation selon ce que l'appelant demande ;
- l'ajout, un ou plusieurs boutons sous la liste ;
- la ligne repliable de chaque élément (le `disclosure`), ouverte pour l'élément qu'on vient d'ajouter ;
- l'état vide.

L'appelant fournirait la ligne de chaque élément (son `label`), son corps (son formulaire), et répondrait aux événements (`move`, `remove`, `add`) en appelant son api. Les deux onglets de Syrtis n'auraient plus qu'à décrire leurs éléments.

## Ce que l'agent du DS peut faire

- Décider si le motif mérite un composant, et lequel : un `block-list`, une variante de `disclosure`, autre chose.
- Réviser l'implémentation actuelle de Syrtis (`widget-slot-editor.vue.twig`, `widget-slot-editor.vue`, `widget-slot-editor.scss`, classe `widget-slot-editor--block`) pour la faire coller au standard retenu : Syrtis prend ce qui est décidé.

## Voir aussi

`inline-sentence.md` : la ligne elle-même, des mots et des marqueurs qui enveloppent.
