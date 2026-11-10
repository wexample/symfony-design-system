# Chat entre utilisateurs (Syrtis) : le DS a-t-il tout ?

Opened: 2026-11-10
Updated: 2026-11-10
Author: agent:main

## Contexte
Syrtis veut un chat entre utilisateurs, sur le même modèle que le chat avec l'agent (symfony-ai-conversation, `abstract-entity-chat`, `abstract-agent-chat`, `thread`, `composer`, `message`). Une conversation aura plusieurs participants — des personnes, et éventuellement des agents —, chaque message son auteur.

## Ce qui existe déjà, côté DS
- `collection/chat/abstract-entity-chat`, `collection/thread`, `composer`, `message` : le fil et la saisie.
- `avatar-group`, `count-badge`, `status-icon`, `item-list`, `bar`, `live-update-status`.

## La question
Dire, composant par composant, ce qui existe, ce qui s'adapte, ce qui manque, pour :
1. **L'auteur de chaque message** dans le fil : avatar et nom d'une personne ou d'un agent, regroupement des messages consécutifs d'un même auteur, ses messages à soi distingués des autres.
2. **Un avatar seul** (pas un groupe) : image, initiales ou emoji à défaut, tailles, et un indicateur de présence (en ligne / absent).
3. **La liste des membres** d'une conversation : avatar, nom, rôle, présence ; ajouter / retirer.
4. **La liste des conversations** (boîte de réception) : titre ou participants, aperçu du dernier message, date, nombre de non-lus.
5. **Dans le fil** : séparateur « nouveaux messages », séparateur de date, indicateur « … est en train d'écrire ».
6. **Dans la saisie** : mention d'un membre (`@`), avec sa liste de suggestions.

## Attendu
Un état des lieux (existe / à adapter / à créer) et, pour ce qui manque, ta proposition de composants génériques — rien de propre à Syrtis.
