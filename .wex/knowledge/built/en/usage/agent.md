# Components an agent may use

The loader reads a page as a tree of nodes and lets an agent act on them (symfony-loader, *What an agent may do in a page*); the design system writes what each component says of itself, in a `.ai.yml` beside its template, and runs its actions in its script. The inventory's *AI* column says which do.

## What each offers

| Component | Kind | Actions |
|---|---|---|
| `switch`, `color-scheme-switch` | `switch` | `get`, `set({checked})`, `toggle` |
| `button`, `button-target` (twig and vue) | `button` | `click` — asks first when the button carries `confirm`; a form sent is answered from the page reached |
| `button-menu` (twig and vue) | `menu-button` | `items`, `open`, `close`, `select({item})` — an item that is a link is answered from the page reached —, `point({item})` |
| `menu-collapsible-panel` | `side-menu` | `expand`, `collapse` |
| `menu-item-collapsible` | `menu-group` | `expand`, `collapse`, `open` |
| `menu-item-link` | `menu-item` | `open` |
| `search-input` | `search` | `get`, `set({value})` — typed in letter by letter —, `clear` |
| `pagination` | `pagination` | `go({page})`, `next`, `previous`, pages counted from one |
| `data-table` (twig and vue) | `table` | `rows`, `sort({column, direction?})`, `reset_sort`, `point_row`, `point_cell`, `point_column`, `clear_filters`, `row_actions({row})`, `run_row_action({row, action})` |
| `modal`, `panel` | `modal`, `panel` | `close` — what they hold are nodes of their own, inside them |
| a form (`form_load`, the loader's `Form`) | `form` | `fill({values})` — by name or by label, one field after the other —, `submit` — pressed through its button when the button is a node, so the press is seen and a confirmation asked —, `errors`; its fields stand inside it. Written, it says `unsaved` in its state, and `fill` and the fields' `set` answer `saved: false`: nothing is kept until it is sent |
| `text-input` and its kinds (email, tel, url, password, number, textarea) | `text-field` | `get`, `set({value})` — typed in letter by letter —, `clear` |
| `select-input` | `select-field` | `get`, `choose({option})` by value or label, `set`, `clear`; the choices in its state |
| `checkbox-input`, `switch-input` | `toggle-field` | `get`, `set({value: boolean})`, `clear` |
| `date-input`, `datetime-input`, `time-input` | `date-field` | `get`, `set({value})` as ISO, `clear` |
| `file-input` | `file-field` | `get` only: a file is the user's to pick |

A row, a cell, an action of a row, an item of a menu are parts of their node, reached through it: `data-table` names rows by `data-row-key` and columns by `data-column-key`, in both twins. The fields of a form, though, are nodes of their own: they differ too much — a text, a choice, a date, a file — for one `fill` to describe them all, so each kind says what it takes, and the form's `fill` only hands the values over in order. `Field` runs the common actions; a kind that writes its value its own way (`select`) overrides them as it overrides `writeValueAssisted`.

## Showing the page

- `layout_agent = true` in the application's layout shows the whole application; a component holds back with `agent: false`.
- `menu_left_agent` shows the side menu alone, with every entry in it.
- `agent: true` on one component, or `agent: ['get']` for some of its actions, where it is drawn.
- `agent_description` gives a component a purpose its label does not say — in english, as the contracts are.

## What the user sees of it

- A press by an agent is the wave a press leaves (`RippleHelper.rippleAgentPress`), in the accent, from the element's middle, the element framed while it spreads. A component running an action that presses something calls it first.
- What an agent points at is framed over the page (`AgentPointer`), with the assisted outline the fields wear; the frames follow scrolling and the element's size, and go when the user acts in their layer of the page: the page's frames stand under the veil of a modal or a panel (`_agent-frame.scss`, `--z-modal` less the veil's slot), a frame drawn in one stands in it.
- The controls demo (*Menus*) shows an entry pressed, one pointed at, and one pressed by an agent.

## The chat

`abstract-entity-chat` runs the tool calls of whatever engine writes in it. A chat says, in the words of its own entity, what a call is, what an answer is, and where to send one:

| Hook | symfony-ai `message-chat` | Syrtis `agent-chat` |
|---|---|---|
| `readToolCall(entity)` | `type === 'tool'`, json in `body` | `contentType === 'tool_call'`, json in `content` |
| `isToolResult(entity)` | `callId` in the body | `name === 'ASYNC_FRONTEND_ACTION_COMPLETE'` |
| `postToolResult(call, callId, results)` | `POST message/create` with `toolResult` | the same |
| `getConversationKey()` | the session id | the session id |

The chat draws a call as *Tool · name*, hides the answers, keeps the wait for the engine's reply across the lines a tool adds, and joins to each answer what the page told the user in toasts meanwhile (`notifications`). What the other side writes is drawn from markdown, raw html shown as text (`MarkdownHelper`); a sound says a message arrived, the reader free to turn it off from the bar above the thread.

## What the user did

The components say what the user does to them, for the agent to read at its next turn (symfony-loader, *What the user did meanwhile*): a switch flipped, a field edited — once, when the user is done with it —, a button pressed, a table searched, sorted or filtered, an item chosen in a menu, a page of a list reached, a form sent. The chat takes the log with whatever it sends next, through `postMessage(content, userEvents)` and `postToolResult(call, callId, results, userEvents)`, and hides the lines the server writes for it (`isUserEvent`); Syrtis's `agent-chat` sends them in the same request as the message, each stamped `user_event`.

## Over a modal

One conversation, shown twice. The dock's chat stands behind a modal's veil; the tools of the dock marked `overlay: true` — the chat — stand again over the veil, at the window's end (`overlay-tools`, drawn by the dashboard layout), the modal or the panel moving over to leave them room. One chat at a time listens to the conversation: the one in front, or the first opened; the one handed it back reads what it missed. The agent sees none of this: it reads the page, the overlay being a node like another.
