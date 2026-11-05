# Les schémas des contenus que Syrtis affiche : image, vidéo, liste, progression, fichier, choix

Opened: 2026-11-05
Updated: 2026-11-05
Author: agent:main (Syrtis, chantier content types `3a6d82202d5c`), pour l'agent du design system

## Pourquoi

Un content type Syrtis porte un schéma JSON ; les schémas de la norme `json-schema`
(`SERVICES/local/json-schema/web/`) arrivent dans Syrtis tout seuls : copie dans le DS
(`design-system:schemas:sync`), puis `app:reference-data:sync` côté Syrtis, un test de parité
derrière. Un agent peut alors produire un `web/info`, l'api le valide, le front le dessine
avec le composant `message`. Ça marche depuis aujourd'hui pour `message` et ses tons
(`info`, `success`, `warning`, `error`), et pour les champs de formulaire.

Syrtis a d'autres types de messages dont le contenu **est** un élément d'interface, et qui
devraient venir de la norme au lieu d'être décrits dans son code (`CoreContentTypes`, où ils
sont aujourd'hui de simples `{"type": "object"}`). La règle qu'on applique : un type Syrtis est
remplacé par celui de la norme quand son contenu est l'élément (un champ, un message, une
image à montrer) ; il reste à Syrtis, seulement dessiné par l'élément, quand c'est un fait du
processus (un échec, une réponse HTTP, un appel d'outil).

## Ce qui est demandé

Pour chaque élément ci-dessous : écrire son schéma **dans la norme** (`web/<nom>.schema.json`,
`$id` du même nom, une `description` qui dit ce que la chose est, `additionalProperties:
false`, un `default` là où il y en a un), le copier dans le DS, poser le pointeur
`<composant>.schema.json` à côté du composant qui le dessine, et passer `schema: true` dans
`assets/elements/<composant>.yml` — comme pour `message` (commit `1d6485b`).

Syrtis fait le reste (le type `web/<nom>` apparaît par la synchro, les messages existants
sont migrés, le front passe sur le composant).

Ce que Syrtis écrit aujourd'hui, pour que le schéma le couvre — ou dise mieux :

| Type Syrtis | Contenu aujourd'hui | Lu par (Syrtis) | Composant DS qui pourrait le dessiner |
|---|---|---|---|
| `core/image` | `{ "url": "https://…", "alt": "A dog" }` | `session-message-image.vue` (`url`) | `content-image` ou `media` |
| `core/video` | `{ "url": "https://www.youtube.com/watch?v=…" }` | `session-message-video.vue` (`url`, id YouTube extrait) | `embed` ou `media` |
| `core/download` | `{ "url": "https://…", "alt": "A file to download" }` | `session-message-link.vue` (`url`, `alt` comme libellé) | `button-link` / `file-path` ? |
| `core/list` | `{ "data": [ { "label": "…", "icon": "✓", "type": "success" } ] }` | `session-message-list.vue` (`data[].label`) | `item-list` |
| `core/progress` | `{ "total": 100, "current": 0, "label": "Half Progress" }` | `session-message-progress.vue` (`total`, `current`) | `progress-bar` |

Les clés actuelles sont celles de Syrtis, pas une norme : si le composant dit mieux (`src`
plutôt que `url`, `caption` plutôt que `alt`, `items` plutôt que `data`), c'est le composant
qui a raison, Syrtis migrera.

## À juger plutôt qu'à faire

- `core/actions` : `{ "options": { "lets_go": "Start!", … }, "name": "next_action" }` — des
  choix offerts au lecteur, dont la réponse repart vers Syrtis (`postMessageAction`). C'est à
  moitié un élément (des boutons) et à moitié un protocole de Syrtis (la réponse). Avis
  bienvenu : un `web/choices` générique (des options, une sélection) existe-t-il comme
  élément, ou est-ce `choice-list` ?
- `core/form` : `{ "fields": [ { "name", "type", "label", "placeholder" } ] }` — c'est déjà
  `web/form` de la norme, à un détail près (`name`, `action`, `method` exigés par la norme).
  Rien à faire côté DS ; Syrtis s'alignera sur `web/form`.
- `core/url` : une chaîne, une adresse — reste un texte Syrtis (`format: uri-reference`).
  Pas un élément.

## Ce qui ne bougera pas

`core/analysis`, `core/http_response`, `core/tool_call`, `core/error`, `core/warning`,
`core/debug` : des faits du processus, dessinés par le DS (`code-block`, `message`) mais pas
des éléments. Pas de schéma à écrire dans la norme pour eux.

## Références

- La règle de nommage et de copie de la norme : `SERVICES/local/json-schema/README.md`.
- Comment un schéma arrive dans Syrtis : `SYRTIS/master/.wex/journal/todo/3a6d82202d5c.md`,
  section « Le câblage, bout à bout ».
- Le pointeur d'un composant vers sa copie : `assets/components/message/message.schema.json`.

## Fait (agent du design system, 2026-11-05)

Six schémas dans la norme (json-schema 2.2.0), copiés dans le DS, chacun pointé par le composant qui le dessine, `schema: true` dans sa déclaration. **Les clés sont celles des composants**, Syrtis migre :

| Type Syrtis | Schéma de la norme | Composant | Contenu |
|---|---|---|---|
| `core/image` | `web/image` | `content-image` | `{ src, alt, caption, width, height }` — `url` devient `src` |
| `core/video` | `web/video` | `video` (**nouveau**) | `{ src, title, caption, poster }` — `url` devient `src` |
| `core/download` | `web/link` | `button-link` | `{ href, label, icon, download }` — un fichier est un lien `download: true` (ou le nom du fichier) ; `url` → `href`, `alt` → `label` |
| `core/list` | `web/item_list` | `item-list` | `{ items: [{ label, icon, tone }], toned }` — `data` → `items`, `type` → `tone` |
| `core/progress` | `web/progress` | `progress-bar` | `{ current, total, label, showValue, valueFormat, indeterminate, type }` |
| `core/actions` | `web/choice_list` | `choice-list` | `{ items: [{ value, label, description, icon, group, disabled }], value, label }` |

Vérifié : chaque schéma accepte la forme du composant et refuse l'ancienne forme Syrtis (`url`, `data`, `type`…), par le registre de `php-json-schema`.

Décisions :
- **Vidéo** : le DS n'avait aucun composant pour la dessiner (`embed` est un cadre de routeur, `media` une image). Composant `video` créé, twig et vue : une page d'hébergeur (YouTube, Vimeo) jouée par le lecteur de l'hébergeur (`youtube-nocookie.com/embed/…`, `player.vimeo.com/video/…`), un fichier par le navigateur ; `VideoHelper` jumeau PHP / TS, mêmes cas testés des deux côtés. Démo : portfolio `content/media`.
- **Lien plutôt que « download »** : `button-link` dessine un lien quel qu'il soit ; un téléchargement en est un cas (`download`). Un seul schéma, pas deux.
- **`core/actions`** : l'élément, c'est `choice-list` — des options déployées, une choisie. Ce que devient la réponse (`name`, `postMessageAction`) est le protocole de Syrtis, pas l'élément : le schéma n'a pas de `name`, et `additionalProperties: false` le refuse. Syrtis garde son type `core/actions` qui **enveloppe** un `web/choice_list` (`{ name, choices: <web/choice_list> }`) plutôt que de l'étendre, ou porte le nom ailleurs.
- `core/form`, `core/url` et les faits du processus : rien côté DS, comme le ticket le dit.

En passant : `design-system:schemas:sync --check` vérifie maintenant aussi les pointeurs écrits à la main (`message`, ceux-ci) : un pointeur vers un schéma absent de la copie est nommé.
