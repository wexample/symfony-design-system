# Vue source annotée, et vue côte à côte source / findings

Opened: 2026-10-06
Updated: 2026-10-06
Author: agent:addon:ai/editor

## Contexte

Les verdicts filestate disent maintenant *où* dans le fichier ils ont trouvé
(`Verdict.locations` : plages `{line, column, end_line, end_column}`, 1-based, fin
exclusive — `filestate/.wex/journal/todo/verdict-locations.md`, fait). Le board les
stocke tels quels (JSON des verdicts sur `ProcessItem`). Il manque de quoi les montrer.

## Décision (opérateur, 2026-10-06)

- Les annotations ne se montrent que dans la **vue source** du fichier — jamais dans le
  rendu (markdown converti, « preview ») : on lit d'un côté, on corrige de l'autre.
- On va vers une sorte d'IDE : la source devient la vue principale pour travailler, la
  preview est secondaire. Prévoir la suite (autocomplétion, etc.).

## Composants demandés

1. **Vue source annotée** — un texte, des annotations `{range, severity, message, code}` :
   - plage surlignée (ton de la sévérité), marque dans la gouttière à la ligne,
     infobulle au survol (message + règle) ;
   - lecture seule d'abord, éditable ensuite (le même composant que l'éditeur de fichier) ;
   - base recommandée : **CodeMirror 6** (décorations, gouttière, *lint diagnostics*
     natifs = nos findings, autocomplétion plus tard) plutôt qu'un surlignage maison.
     Probablement dans **symfony-coding** (qui a déjà `console` et `code-input`), le DS
     n'en gardant que le style.
2. **Vue côte à côte** (« sided views ») — la source d'un côté, la liste des findings de
   l'autre, synchronisées : clic sur un finding → la source défile jusqu'à la plage et la
   met en avant ; survol d'une plage → le finding s'éclaire. Repliable (une colonne sur
   écran étroit, dans un panel).
3. Une API simple pour le board : passer le texte, le langage (extension) et la liste
   d'annotations ; un événement quand on clique une annotation.

## Critère de fin

Un fichier markdown avec trois occurrences d'un mot interdit s'affiche en vue source avec
les trois plages surlignées, trois marques en gouttière et leurs infobulles ; en vue côte à
côte, cliquer le finding amène à la plage.

## Lien

Le board organise les vues (lecture / source) : todo du board « Vues d'un fichier :
lecture et source ».
