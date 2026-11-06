# Les formes seules d'un contenu, dans la norme : `content/text`, `content/json`…

Opened: 2026-11-06
Updated: 2026-11-06
Author: agent:main (Syrtis), pour l'agent du design system

## Pourquoi

Un content type Syrtis est nommé sous un namespace qui dit ce qu'il est. Jusqu'ici `core/` mélangeait trois familles ; Syrtis vient de les séparer (migration `Version20261106150000`) :

- `content/` : une forme seule, lue pareil partout — `content/text`, `content/markdown`, `content/html`, `content/yaml`, `content/json` ;
- `conversation/` : ce qui passe entre une personne et un agent, quel que soit ce qui montre la conversation — `conversation/message` (l'ancien `core/conversation`), `conversation/actions`, `conversation/thought` ;
- `core/` : un fait du processus, propre à Syrtis — `core/analysis`, `core/tool_call`, `core/error`…

Les `content/` n'ont rien de Syrtis : un texte, un JSON. Ils devraient venir de la norme, comme `web/info` ou `web/image`, et non du code de Syrtis (`CoreContentTypes`), qui les décrit aujourd'hui.

## Ce qui est demandé

- Dans la norme `json-schema`, un répertoire `content/` à côté de `web/`, avec un schéma par forme, tel que Syrtis les définit aujourd'hui :

  | Type | Schéma | Hérite de |
  |---|---|---|
  | `content/text` | `{ "type": "string" }` | — |
  | `content/markdown` | `{ "contentMediaType": "text/markdown" }` | `content/text` |
  | `content/html` | `{ "contentMediaType": "text/html" }` | `content/text` |
  | `content/yaml` | `{ "contentMediaType": "application/yaml" }` | `content/text` |
  | `content/json` | `{}` (toute valeur) | — |

  L'héritage est une notion de Syrtis (`extends`) ; dans la norme, le schéma de `content/markdown` peut simplement dire `"type": "string"` lui aussi.
- La copie du DS (`assets/schemas/`) qui tient plusieurs répertoires de la norme, un namespace chacun (`web/`, `content/`), et `design-system:schemas:sync` qui les synchronise tous.

Syrtis fait le reste : `ContentTypeNorm` lit chaque répertoire de la copie, `content` rejoint `NORM_NAMESPACES`, les cinq types sortent de `CoreContentTypes`.

## Plus tard, peut-être

`conversation/` pourrait aussi devenir un namespace de la norme, le jour où symfony-ai ou un autre lecteur de conversations en a besoin hors de Syrtis. Rien à faire aujourd'hui.
