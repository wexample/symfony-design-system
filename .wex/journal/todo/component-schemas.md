# Écrire le JSON schema de chaque composant

Opened: 2026-10-08
Updated: 2026-10-08
Author: agent:main

## Pourquoi

Un modèle de données uniforme pour tous les composants, formulaires et champs
compris : ce qu'un composant prend, avec types et défauts. C'est le préalable
d'un page builder (WYSIWYG où l'on place n'importe quel composant, un formulaire
n'en étant qu'un cas) : une fois les schémas écrits, le builder est surtout un
éditeur générique de ces données.

Le form builder de network (`NETWORK/archeo/proposed-packages/dismiss/symfony-form-builder`)
est classé : ~590 lignes jamais en prod, quatre types de champs, rien à
récupérer que l'idée. Il renaît comme cas particulier du page builder.

## Ce qui existe

- `Enum\ElementFormat::SCHEMA` : le format est prévu, fichier
  `components/<nom>/<nom>.schema.json`, « the contract … every other renderer is
  checkable against it ». L'inventaire (`ElementScannerService`,
  `ElementRegistryService`) a déjà sa colonne.
- Aucun schéma écrit : 0 fichier `*.schema.json` sous `assets/components`.

## À faire

1. Fixer la forme d'un schéma : JSON Schema (draft 2020-12), les options du
   composant en `properties`, les défauts en `default`, ce qui est traduit marqué
   comme tel, les slots / contenus imbriqués décrits (un composant qui en
   contient d'autres).
2. Écrire les schémas, en commençant par la famille `form/*` (inputs) puis les
   composants de contenu.
3. Faire de l'inventaire le contradicteur : un schéma qui ne correspond pas aux
   options lues par le template ou la fonction twig, signalé.
4. Ensuite seulement : le page builder (package à créer), qui stocke des arbres
   de composants validés par ces schémas, et enregistre les réponses quand un
   bloc est un formulaire.
