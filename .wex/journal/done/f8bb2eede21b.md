# Chat entre utilisateurs (Syrtis) : le DS a-t-il tout ?

Opened: 2026-11-10
Updated: 2026-11-10
Status: done
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

## Reply

**Verdict: the thread and the composer are there; what a conversation between people adds is mostly missing — an avatar of its own, presence, the lists, the dividers, mentions. All of it generic; nothing for Syrtis alone.**

| Need | State | What |
| --- | --- | --- |
| 1. Author of each message | exists, to adapt | `thread-message` draws avatar, author, date, and one's own (`own`, laid at the end side by `thread--aligned`). To add: consecutive messages of one author grouped (`thread-message--continued`: no avatar, no name, closer), worked out by the thread; an agent told apart by a marker beside its name (`authorMarker`). |
| 2. An avatar alone | to create | The `.avatar` shape and `avatar()` (a generated face) exist, `avatarInitials` too (avatar-group). Missing: an `avatar` component — picture, else initials, else an emoji —, sizes, and presence: a dot on its corner (`online`, `away`, `offline`), the tones of `led`. `avatar-group` and `thread-message` then draw theirs with it. |
| 3. Members of a conversation | to create | Nothing fits: `item-list` is static, `block-list` folds. A `person-list`: a row per person — avatar with presence, name, role as a marker, actions at the row's end (remove) —, add under it. People of a project, of a team, as much as of a conversation. |
| 4. Conversations (inbox) | to create | A `conversation-list`: a row per conversation — its avatar or its participants' (`avatar-group`), its title, the last message on one line, its date (`date-display`, relative), its unread count (`count-badge`), unread ones in strong. Mail, tickets, chats alike. |
| 5. In the thread | to create / exists | A divider shape `thread--divider`: a rule, a word in its middle — a day, « New messages » in the selected tone. « … is typing »: `thread--activity` already is the other side at work, at the thread's foot; it takes a name and the `dots`. |
| 6. Mentions in the composer | to create | `composer` gains `mentions` — a source like `tags-input`'s (`suggestionsUrl`) —: `@` opens the people matching what follows, chosen goes in as `@name`, drawn in the sent message as a marker. The most work of all: the caret in a textarea. |

Order proposed, smallest first: avatar with presence, grouped authors and the divider; then the two lists; mentions last. Each with its twig and vue twins, a demo, tests.

Waiting for a go (the owner's) before building.

## Built (go given)

1. **`avatar`** (9c3116b), twig and vue: a picture, else a drawn face (`seed`, twig), else an emoji, else the initials; `size`; `presence` — `online`, `away`, `busy`, `offline` — a dot on its corner, said in words to a screen reader.
2. **The thread between people** (9c3116b), on `abstract-entity-thread`:
   - a line soon after its author's (5 min, same side) goes on from it: no face, no name again (`thread--item--continued`);
   - `dayDividers`: a divider where a day begins — today, yesterday, a date —; `unreadSince`: « New messages » above the first line from someone else after it;
   - projections `getMessageAvatar(entity)` → `{ label, src, emoji, presence }` (the face in place of the icon) and `getMessageAuthorMarker(entity)` → `{ label, tone }` (an agent told apart).
3. **`conversation-list`** and **`person-list`** (22b45b6), twig and vue. The first: `items` `[{ id, title, href, preview, date, unread, avatar | people }]`, `activeId`, `select` for a row with no address. The second: `items` `[{ id, label, src, emoji, presence, role, roleTone }]`, `removable` → `remove`, slots `actions` and `add`.
4. **Mentions** (b4a3119): `composer` `mentions` `[{ id, label, handle, src, emoji }]` — `@` offers the matching ones, arrows, Enter/Tab, Escape —; `submit(content, files, { mentions })` hands the people named. A chat passes its `people` to both: the composer offers them, the thread sets the names apart (`.mention`).

Demo: Content › Avatars (presence), Data › Chat (between people; who and where; the vue chat with mentions). Tests: `thread-people`, `people-lists`, `mentions`.

## For Syrtis

- The chat's entity: `getMessageAvatar` and `getMessageAuthorMarker` from the message's author (a person, or an agent marked `agent`); `isOwnMessage` from the reader.
- `people` on the chat: the conversation's participants; `postMessage(content, files, { mentions })` sends the ids named, for the notification.
- `unreadSince` from the reader's last read; `dayDividers` on.
- The inbox: `conversation-list` with `unread` and `activeId`; the members: `person-list` with `presence` from Mercure.
